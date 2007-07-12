<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @datel		2007/07/07

 *	FIXME zixia: 里面使用了大量的 9999，代表选择出符合条件的所有。未来数据量增大后可能会带来 BUG
 */

/**
 * JiWai.de Database Class
 */
class JWDB_Cache implements JWDB_Interface, JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDBCache
	 */
	static private $msInstance;

	/**
	 *	Instance of JWMemcache
	 *
	 *	@var JWMemcache
	 */
	static private $msMemcache;


	/**
	 *	大数据集的个数阀值
	 *
	 *	对大数据集，即使我们可以永久 cache，也只应该  cache 一段时间，节省资源。
	 *	除非这个数据集非常的常用
	 *
	 *	目前设置： 1000 个
	 */
	const NUM_LARGE_DATASET = 1000;

	/**
	 *	降低并发访问数据库的 Cache 时间
	 *
	 *	对于同样的数据库请求，我们在 MIN_EXPIRE_SECENDS 中，只获取一次
	 *	这样设计的假设前提：我们现在假设秒级的 cache 时间不会带来实时性数据问题
	 *
	 *	目前设置： 1s
	 */
	const TEMPORARY_EXPIRE_SECENDS = 1;


	/**
	 *	可以永久 Cache ，但是数据量很大，永久 Cache 会浪费资源，所以给出一个合理的过期时间
	 *
	 *	比如一个用户有 10000000 条更新，我们取 id 的时候，看最后一页的时候会全部取出来。
	 *	这时候就没有必要一直 cache，不会一直有人看的。超时过期即可
	 *
	 *	目前设置： 3600s = 1Hour
	 */
	const PERMANENT_EXPIRE_SECENDS_LARGE_DATA	= 3600;


	/**
	 *	永久 Cache 时间
	 *
	 *	我们认为可以永久 Cache 的数据，使用这个过期时间。
	 *	这样作主要是为了方便未来调试：如果发现Cache逻辑不对，未能有效过期，可以方便的修改这个时间进行测试
	 *
	 *	864000 - 10天
	 */
	const PERMANENT_EXPIRE_SECENDS = 864000;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWDBCache
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		self::$msMemcache = JWMemcache::Instance();
	}

	/**
	* Destructing method, write everything left
	*
	*/
	function __destruct()
	{
	}


	/**
	 *	对 DbRow 的 memcache 通用处理函数
	 *
 	 *	@param	string				$table		数据库表名
 	 *	@param	array				$ids		数据库主键 id 的数组列表
 	 *	@param	callable function	$function	如果 memcache 中没有数据，则通过这个函数进行获取
 	 *	@param	bool				$forceReload	强制刷新 memcache
 	 *
	 */
	static public function GetCachedDbRowsByIds($table, $ids, $function=null, $forceReload=false)
	{
		if ( empty($function) )
			$function = array( ("JW".$table), "GetDbRowsByIds");

		if ( ! method_exists($function) )
			throw new JWException("function $function[0]::$function[1] not exists.");

 		/*
		 *	Stage 1. 根据 ids 获取 memcache 的 keys
		 */
		$mc_keys 		= self::GetCacheKeysByIds('Status', $idStatuses);

		$key_map_mc2db	= array_combine($mc_keys	,$idStatuses);

		/*
		 *	Stage 1.1 通过 memcache 尝试获取数据
		 */
		$hit_mc_rows = self::$msMemcache->Get($mc_keys);
		$hit_mc_keys = array_keys($hit_mc_rows);

		$hit_ids = JWFunction::GetMappedArray($hit_mc_keys, $key_map_mc2db);

		/*
		 *	Stage 1.2 将 memcache 返回数据中的 memcache key 转化为 db key
		 */
		$hit_db_rows = array();
		foreach ( $hit_mc_keys as $hit_mc_key )
		{
			$hit_db_rows[ $key_map_mc2db[$hit_mc_key] ] = $hit_mc_rows[$hit_mc_key];
		}

		/*
		 *	Stage 1.3 如果 memcache 返回了所有数据，则完全命中，返回数据后结束。
		 */
		if ( count($hit_ids)==count($idStatuses) )
		{
			return $hit_db_rows;
		}


		/*
		 *	Stage 2. memcache 没有完全命中（或没有命中），我们需要从数据库中获取数据，先算出哪些 id 没有命中 cache
		 */
		$unhit_ids	= array_diff($idStatuses, $hit_ids);
		$unhit_mc_keys		= array_diff($mc_keys	, $hit_mc_keys);

		/*
		 *	Stage 2.1 准备开始获取数据。为了避免同一台服务器上多个进程同时获取memcache数据，我们在进入之前设置一个 mutex 
		 */
		$mutex = new JWMutex( array($table,$unhit_ids) );
		$mutex->Acquire();

		/*
		 *	Stage 2.2 成功获取 Mutex，这时候再次检查刚刚 unhit 的数据是否现在已经有了。因为在获取 Mutex 的时候，可能有其他进程已经将数据进行设置了。
		 */
		$retry_hit_mc_rows 	= self::$msMemcache->Get($unhit_mc_keys);

		/*
		 *	Stage 2.3 如果得到了完整数据，则释放 Mutex，返回数据
		 */
		if ( count($retry_hit_mc_rows)==count($unhit_mc_keys) )
		{
			$mutex->Release();

			foreach ( $unhit_mc_keys as $unhit_mc_key )
			{
				$hit_db_rows[ $key_map_mc2db[$unhit_mc_key] ] = $retry_hit_mc_rows[$unhit_mc_key];
			}

			return $hit_db_rows;
		}

		/*
		 *	Stage 2.4 仍然没有完整数据，则进行数据库查询 TODO 刚刚有可能能得到几个数据，可以再次 diff 提高性能
		 */
		$db_rows = call_user_func($function,$unhit_ids);

		foreach ( $unhit_mc_keys as $unhit_mc_key )
		{
			self::$msMemcache->Set($unhit_mc_key, $db_rows[ $key_map_mc2db[$unhit_mc_key] ]);

			$hit_db_rows[ $key_map_mc2db[$unhit_mc_key] ] 	= $db_rows[ $key_map_mc2db[$unhit_mc_key] ];
		}

		/*
		 *	Stage 2.5 释放 Mutex，返回完整数据
		 */
		$mutex->Release();

		/*
	 	 *	Stage 3. 返回完整数据
		 */
		return $hit_db_rows;
	}

	/**
	 *	获取 $mcKey 的值：如果在 memcache 中，就直接返回；如果不在，就通过函数得到结果，设置 memcache 后返回。
	 *	使用了 mutex 防止多个进程设置同一个 mcKey
	 *
	 *	@param	string			$mcKey		memcache key
	 *	@param	string or array	$function 	callback function, 如果 memcache 中没有 $mcKey 的值，则通过调用 $function 得到的返回值进行设置
	 *	@param	string or array	$param		callback function 的参数
	 *	@param	int				$timeExpire	memcache 过期时间
	 *	@param	bool			$forceReload	不管是否已经有 cache，强制重新 Load，刷新 memcache 的值
	 */
	static function GetCachedValueByKey($mcKey, $function, $param, $timeExpire=self::TEMPORARY_EXPIRE_SECENDS, $forceReload=false)
	{
		self::Instance();

		$mc_val = null;

		if ( !$forceReload )
			$mc_val = self::$msMemcache->Get($mcKey);

		if ( !empty($mc_val) )
			return $mc_val;

		/*
		 *	防止多个进程在重负载下并发同时设置同一个 key
		 *	（比如被 sina 首页连接了我们的某一个用户）
		 *
		 *	如果 memcache 中没有 key，那么先 acquire 一个 mutex
		 *	获取后，再检查是否 key 刚刚被其他进程设置了：
				如果设置了，则 release mutex 然后返回数据
				如果没有设置，则进行数据库操作设置，然后 release mutex，返回数据
		 *
		 *	这样保证，在同一台前端服务器上，对应于某一个 key，最多只有一个进程进行 memcache 的更新
		 */

		$mutex = new JWMutex($mcKey);
		$mutex->Acquire();

		/**
		 *	检查一下，在我们获取到 Mutex 之前，是否已经被其他进程设置好了数据。
		 */
		if ( !$forceReload )
			$mc_val = self::$msMemcache->Get($mcKey);

		/**
		 *	如果 Cache was set just before we acquired the mutex，释放 Mutex 后返回数据
		 */
		if ( !empty($mc_val) )
		{
			$mutex->Release();
			return $mc_val;
		}

		/**
		 *	从数据库中获取数据
		 */
		if ( ! method_exists($function) )
			throw new JWException("function $function[0]::$function[1] not exists.");

		$db_result = call_user_func($function,$param);

		self::$msMemcache->Set($key, $db_row, 0, $timeExpire);

		/**
		 *	释放 Mutex
		 */
		$mutex->Release();

		return $db_result;
	}


	static public function GetMaxOffset($offset)
	{
		if ( $offset<100 )
			$offset = 100;
		elseif ( $offset<1000 )
			$offset = 1000;
		else
			$offset = ( intval($offset/1000)+1 ) * 1000;

		return $offset;
	}

	/*
	 *	@param	string	SQL
	 *	@param	bool	need return more then one row?
	 *	@return	array	row or array of rows
	 */
	static public function GetQueryResult( $sql, $moreThanOne=false, $forceReload=false )
	{
		self::Instance();

		/*	7/10/07 zixia: cache 构思
				1. 只能通过逻辑进行过期(在 JWDB_Cache_${TableName} 类中实现
				2. 同时保证，对于同一个 $sql，在1s种内，只会被执行1次（我们假设1s不会影响用户对实时更新的感觉）
	 	 */

		$mc_key = self::GetCacheKeyByFunction("JWDB::GetQueryResult", array($sql,$moreThanOne));

		return self::GetCachedValue(	 $mc_key
										,array('JWDB','GetQueryResult')
										,array($sql,$moreThanOne)
										,self::TEMPORARY_EXPIRE_SECENDS
									);
	}


	static public function SaveTableRow( $table, $condition )
	{
		self::Instance();

		$inserted_id = JWDB::SaveTableRow($table,$condition);

		$db_row	= self::GetTableRow($table, array('id'=>$inserted_id), 1);

		self::OnDirty($db_row,$table);	

		return $inserted_id;
	}


	/*
	 * 根据条件删除。
	 * @param condition array key为col name，val为条件值，多个条件的逻辑关系为AND
	 * @return bool
	 */
	static public function DelTableRow( $table, $condition )
	{
		self::Instance();

		$db_rows 	= self::GetTableRow( $table, $condition, 9999 );
		$pk_ids		= JWFunction::GetColArrayFromRows($db_rows,'id');

		// 注意顺序：先操作数据库，然后再去 OnDirty
		$ret = JWDB::DelTableRow($table, $condition);

		foreach ( $pk_ids as $pk_id )
		{
			self::OnDirty($db_rows[$pk_id], $table);
		}
			
		return $ret; 
	}


	/*
	 *	所有的数据表都有 id 字段（PK）
	 * 	@return 	id 主键的值，或者0
	 */
	static public function ExistTableRow( $table, $condition )
	{
		self::Instance();

		$db_row = self::GetTableRow($table, $condition, 1);

		if ( empty($db_row) )
			return 0;

		return $db_row['id'];
	}


	/*
	 * @return bool
			succ / fail
	 */
	static public function ReplaceTableRow( $table, $condition)
	{
		self::Instance();

		//. FIXME 9999 是一个假想最大值，应该更准确
		$db_rows 	= self::GetTableRow( $table, $condition, 9999 );
		$pk_ids		= JWFunction::GetColArrayFromRows($db_rows,'id');

		/*
		 *	更新老数据
		 */
		// 注意顺序：先操作数据库，然后再去 OnDirty
		$ret = JWDB::ReplaceTableRow($table, $condition);

		foreach ( $pk_ids as $pk_id )
		{
			self::OnDirty($db_rows[$pk_id], $table);
		}
			

		/*
		 *	更新新数据
		 */
		$db_rows 	= self::GetTableRow( $table, $condition, 9999 );
		$pk_ids		= JWFunction::GetColArrayFromRows($db_rows,'id');

		foreach ( $pk_ids as $pk_id )
		{
			self::OnDirty($db_rows[$pk_id], $table);
		}
	
		return $ret; 
	}


	/*
	 * @return bool
			succ / fail
	 */
	static public function UpdateTableRow( $table, $idPk, $condition)
	{
		self::Instance();

		$db_row	= self::GetTableRow( $table, array('id'=>$idPk) );
		$ret 	= JWDB::UpdateTableRow($table, $idPk, $condition);

		// 更新老数据
		self::OnDirty($db_row, $table);

		// 更新新数据
		$db_row 	=  JWDB::GetTableRow($table, array('id'=>$idPk));
		self::OnDirty($db_row, $table);

		return $ret;
	}


	/*
	 * 	@return 	array/array of array	$row/$rows
	 */
	static public function GetTableRow( $table, $condition, $limit=1 )
	{
		self::Instance();

		/**
		 *	有数据库主键！可以作行一级的永久 Cache
		 */
		if ( isset($condition['id']) )
		{
			$mc_key 	= self::$msMemcache->GetKeyFromPk($table, $condition['id']);

			return self::GetCachedValue(	 $mc_key
											,array('JWDB','GetTableRow')
											,array($table,$condition,$limit)
											,self::PERMANENT_EXPIRE_SECENDS
										);

		}

		/*
		 *	条件中没有主键，不知道如何更新 cache 所以不能 cache（直到我们想明白如何将通用的条件查询转换为对 memcache 数据的更新）
		 *	这样过期可能会带来不同步的问题，先不 cache.
		 */
		$mc_key	= self::GetCacheKeyByCondition($table,$condition,$limit);

		return self::GetCachedValue(	 $mc_key
										,array('JWDB','GetTableRow')
										,array($table,$condition,$limit)
										,self::TEMPORARY_EXPIRE_SECENDS
									);

	}

	/*
	 *	查找某个查询中最大的 id
	 *	功能说明：有些时候我们根据一个条件，查找到了相关的id。但是还需对这些 id 做一些运算，得到结果。
					为了 cache 结果，我们需要知道条件查询的 id 是否有变化。所以用这个max id作为版本号
	 */
	static public function GetMaxId($table, $condition)
	{
		self::Instance();

		$mc_key = JWMemcache::GetKeyFromFunction("JWDB::GetMaxId", $condition);

		return self::GetCachedValue(	 $mc_key
										,array('JWDB','GetMaxId')
										,array($table,$condition)
										,self::TEMPORARY_EXPIRE_SECENDS
									);
	}


	static public function OnDirty($dbRow, $table=null)
	{
		switch ( $table )
		{
			case "Picture":
				return JWDB_Cache_Picture::OnDirty($dbRow);
				break;
			case "User":
				return JWDB_Cache_User::OnDirty($dbRow);
				break;
			case "Status":
				return JWDB_Cache_Status::OnDirty($dbRow);
				break;
			default:
				throw new JWException("JWDBCache::OnDirty($dbRow[id], $table) not support yet!");
				break;
		}
		throw new JWException('some err occoured! you should not reach here!');
	}


	/**
	 *	根据表名、idPk、条件选择、功能函数名，组合出一个唯一的 memcache key
	 *
	 *	如：
			TB:User(id=1)
			TB:User(nameScreen=zixia)
			TB:Device(address=zixia@zixia.net,type=gtalk)

			FN:JWStatus::GetStatusIdsFromUser(1)
			FN:Status::GetStatusIdsFromUser(1)
	 *
	 *
	 */

	/**
	 *	返回 db ids 映射的 memcache ids
	 *
	 *	@param	string			$table	数据库表名
	 *	@param	array of int	$idPks	数据库表的主键数组
	 *	@return	array of string	$mc_keys	memcache 的 keys，和参数是一一对应关系
	 */
	static public function GetCacheKeysByIds($table, $idPks)
	{
		$keys = array();
		foreach ( $idPks as $pk_id )
		{
			$keys[] = "TB:$table(id=$pk_id)";
		}
		return $keys;
	}

	/**
	 *	将数据库主键映射为 memcache key
	 *
	 */
	static public function GetCacheKeyById($table, $idPk)
	{
		$mc_keys = self::GetCacheKeysByIds($table,array($idPk));
		return $mc_keys[0];
	}

	/**
	 *	根据一个条件得到 key：只能作防止并发的 TEMPORARY_EXPIRE_SECENDS 设置，因为目前还没有清晰的逻辑能够正确的使其过期
	 *
	 */
	static public function GetCacheKeyByCondition($table, $condition, $limit)
	{
		$condition 	= sort($condition);

		$mc_key 	= "TB:$table(" . serialize($condition) . "):$limit";

		return $mc_key;
	}

	static public function GetCacheKeyByFunction($function, $param=null)
	{
		$param 		= sort($param);

		$mc_key 	= "FN:$function(" . serialize($param) . ")";

		return $mc_key;

	}


}
?>
