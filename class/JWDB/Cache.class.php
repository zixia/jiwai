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
class JWDB_Cache  extends JWDB implements JWDB_Interface, JWDB_Cache_Interface
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
		self::Instance();

		if ( empty($ids) )
			return array();

		if ( empty($function) )
			$function = array( ("JW".$table), "GetDbRowsByIds");

 		/*
		 *	Stage 1. 根据 ids 获取 memcache 的 keys
		 */
		$mc_keys 		= self::GetCacheKeysByIds($table, $ids);

		// to seek: why settype? by zixia
		settype( $mc_keys , 'array' );
		settype( $ids, 'array' );

		$key_map_mc2db	= array_combine($mc_keys	,$ids);

		/*
		 *	Stage 1.1 通过 memcache 尝试获取数据
		 */
		$hit_mc_rows = self::$msMemcache->Get($mc_keys);
		$hit_mc_keys = array_keys($hit_mc_rows);

		$hit_ids = JWFunction::GetMappedArray($hit_mc_keys, $key_map_mc2db);

//XXX
//die(var_dump($function));
//return call_user_func($function,$ids);
/*
(var_dump($key_map_mc2db));
(var_dump($hit_mc_keys));
die(var_dump($hit_ids));
*/
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
		if ( count($hit_ids)==count($ids) )
		{
			return self::SortArrayByKeyOrder( $hit_db_rows, $ids );
		}

		/*
		 *	Stage 2. memcache 没有完全命中（或没有命中），我们需要从数据库中获取数据，先算出哪些 id 没有命中 cache
		 */
		$unhit_ids		= array_diff($ids		, $hit_ids);
		$unhit_mc_keys	= array_diff($mc_keys	, $hit_mc_keys);


		/**
		 * important for un unique ids, thus will cause count(hit_ids) != count(ids),  but unhit_mc_keys is empty!!!
		 * add by seek@jiwai.com 2008.1.19
		 */
		if ( empty($unhit_mc_keys) )
			return self::SortArrayByKeyOrder( $hit_db_rows, $ids );

		if ( ! method_exists($function[0],$function[1]) )
			throw new JWException("function $function[0]::$function[1] not exists.");

		/*
		 *	Stage 2.1 准备开始获取数据。为了避免同一台服务器上多个进程同时获取memcache数据，我们在进入之前设置一个 mutex 
		 */
		$mutex = new JWMutex( $unhit_mc_keys );
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

			return self::SortArrayByKeyOrder( $hit_db_rows, $ids );
		}

		/*
		 *	Stage 2.4 仍然没有完整数据，则进行数据库查询 TODO 刚刚有可能能得到几个数据，可以再次 diff 提高性能
		 */
		$db_rows = call_user_func($function,$unhit_ids);

		foreach ( $unhit_mc_keys as $unhit_mc_key )
		{
			/* for unhit_mc_key, maybe deleted in physics db; */
			if ( false == isset( $key_map_mc2db[ $unhit_mc_key ] ) )
				continue;

			if ( false == isset( $db_rows[$key_map_mc2db[$unhit_mc_key]] ) )
				continue;

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
		return self::SortArrayByKeyOrder( $hit_db_rows, $ids );
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

		$mc_val = false;

		if ( !$forceReload )
			$mc_val = self::$msMemcache->Get($mcKey);

		if ( false!==$mc_val)
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

/*
$mutex->Release();
return call_user_func_array($function,$param);
*/
		/**
		 *	检查一下，在我们获取到 Mutex 之前，是否已经被其他进程设置好了数据。
		 */
		if ( !$forceReload )
			$mc_val = self::$msMemcache->Get($mcKey);

		/**
		 *	如果 Cache was set just before we acquired the mutex，释放 Mutex 后返回数据
		 */
		if ( false!==$mc_val)
		{
			$mutex->Release();
			return $mc_val;
		}

		/**
		 *	从数据库中获取数据
		 */
		if ( ! method_exists($function[0],$function[1]) )
			throw new JWException("function $function[0]::$function[1] not exists.");

		$db_result = call_user_func_array($function,$param);

/*
if ( $function[1] == 'GetStatusIdsFromFriends' && $forceReload )
die(var_dump($db_result));
*/

		self::$msMemcache->Set($mcKey, $db_result, 0, $timeExpire);

		/**
		 *	释放 Mutex
		 */
		$mutex->Release();

		return $db_result;
	}


	/**
	 *	根据 $num 推算出一个大于 $num 的数字，为下次访问做预取。
	 *	@param	int	$num	
	 *
	 *	@seealso	IsCachedCountEnough
	 *
	 *	注意： 这个函数中的逻辑，返回的 $max_num 必须可以被 100 整除！
	 *			目的是为了解决在翻页中，到了最后一页的时候，系统希望获取的 $num + $start 一定是大于实际数据库中存在的数据的。
	 *			我们可以通过 memcache 中的数据个数是否可以被 100 整除，来判断是否还有后续的数据。
	 */
	static public function GetMaxCacheNum($num)
	{
		if ( $num<100 )
			$num = 100;
		elseif ( $num<1000 )
			$num = 1000;
		else
			$num = ( intval($num/1000)+1 ) * 1000;

		//这个函数中的逻辑：返回的 $max_num 必须可以被 100 整除！ (see also IsCachedCountEnough)
		assert($num%100==0);

		return $num;
	}

	
	/**
	 *	检查从 memcached 中取出来的数据是否足够我们所需
	 *	因为我们在存 cache 的时候，会忽略掉 $num,$start，直接将 self::GetMaxCacheNum($start+$num) 个数据存入 cache
	 *	这个时候，下次再从 memcache 中获取 cache 数据的时候，可能得到的数据不足，所以需要判断。
	 *
	 *	@param	int		$numCached	在 memcache 中取到的数据个数
	 *	@param	int		$numNeeded	需要的个数
	 *	@return	bool	$is_enough	是否足够
	 *	@seealso	GetMaxCacheNum
	 */
	static public function IsCachedCountEnough($numCached,$numNeeded)
	{
//echo "IsCachedCountEnough($numCached,$numNeeded)";
		if ( empty($numCached) )
		{
			// cache 里面什么都没有，意味着上次 cache 的时候，从数据库中获取的结果集就是空。不用再次重新查找
			return true;
		}

		if ( $numCached % 100 )
		{
			// 不能被 100 整除，代表在上次获取数据存入 memcache 的时候，已经得到了数据库的最后一页数据
			// 所以，不用比较了，直接返回 enough 
			return true;
		}

		// 判断 cached 数据是否足够我们所需
		return $numCached >= $numNeeded;
	}


	/*
	 *	@param	string	SQL
	 *	@param	bool	need return more then one row?
	 *	@return	array	row or array of rows
	 */
	static public function GetQueryResult( $sql, $moreThanOne=false, $forceReload=false )
	{
//return JWDB::GetQueryResult($sql,$moreThanOne);
		self::Instance();


		// call back function & param
		$ds_function 	= array('JWDB','GetQueryResult');
		$ds_param		= array($sql,$moreThanOne);
		// param to make memcache key
		$mc_param		= $ds_param;


		/*	7/10/07 zixia: cache 构思
				1. 只能通过逻辑进行过期(在 JWDB_Cache_${TableName} 类中实现
				2. 同时保证，对于同一个 $sql，在1s种内，只会被执行1次（我们假设1s不会影响用户对实时更新的感觉）
	 	 */

		$mc_key = self::GetCacheKeyByFunction($ds_function,$mc_param);

		//return JWDB::GetQueryResult($sql,$moreThanOne);

		return self::GetCachedValueByKey(	 $mc_key
											,$ds_function
											,$ds_param
											,self::TEMPORARY_EXPIRE_SECENDS
										);
	}


	static public function SaveTableRow( $table, $condition )
	{
		self::Instance();

		$inserted_id = JWDB::SaveTableRow($table,$condition);

		$db_row	= self::GetTableRow($table, array('id'=>$inserted_id), 1);

		JWSearch::LuceneUpdate( $table, $inserted_id, false );

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

		// 注意顺序：先操作数据库，然后再去 OnDirty
		$ret = JWDB::DelTableRow($table, $condition);

		foreach ( $db_rows as $db_row )
		{
			self::OnDirty($db_row, $table);
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
/* 7/14/07 zixia: 作废，无法正确得到 OnDirty 的 db_row
	static public function ReplaceTableRow( $table, $condition)
	{
		self::Instance();

		//. FIXME 9999 是一个假想最大值，应该更准确
		$db_rows 	= self::GetTableRow( $table, $condition, 9999 );
		$pk_ids		= JWFunction::GetColArrayFromRows($db_rows,'id');

		 //	更新老数据
		// 注意顺序：先操作数据库，然后再去 OnDirty
		$ret = JWDB::ReplaceTableRow($table, $condition);

		foreach ( $pk_ids as $pk_id )
		{
			self::OnDirty($db_rows[$pk_id], $table);
		}
			

		 //	更新新数据
		$db_rows 	= self::GetTableRow( $table, $condition, 9999 );
		$pk_ids		= JWFunction::GetColArrayFromRows($db_rows,'id');

		foreach ( $pk_ids as $pk_id )
		{
			self::OnDirty($db_rows[$pk_id], $table);
		}
	
		return $ret; 
	}
*/


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

		JWSearch::LuceneUpdate( $table, $idPk, false );

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
			$mc_key = self::GetCacheKeyById($table, $condition['id']);

			// 这里只获取一行，存入 memcache。因为有 id 主键，也只会有一行。
			$mc_val	= self::GetCachedValueByKey(	 $mc_key
													,array('JWDB','GetTableRow')
													,array($table,$condition,1)
													,self::PERMANENT_EXPIRE_SECENDS
											);

			// 通过 $limit 是否 > 1 来决定返回 db_row 还是 db_rows
			if ( 1==$limit )
				return $mc_val;
				
			return array($mc_val);
		}

		/*
		 *	条件中没有主键，不知道如何更新 cache 所以不能 cache（直到我们想明白如何将通用的条件查询转换为对 memcache 数据的更新）
		 *	这样过期可能会带来不同步的问题，先不 cache.
		 */
		$mc_key	= self::GetCacheKeyByCondition($table,$condition,$limit);

		return self::GetCachedValueByKey(	 $mc_key
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

		// call back function & param
		$ds_function 	= array('JWDB','GetMaxId');
		$ds_param		= array($condition);
		// param to make memcache key
		$mc_param		= $ds_param;

		$mc_key = self::GetCacheKeyByFunction($ds_function,$mc_param);

		return self::GetCachedValueByKey(	 $mc_key
											,$ds_function
											,$ds_param
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
				$db_cache_user = JWDB_Cache_User::Instance();
				return $db_cache_user->OnDirty($dbRow);
				break;
			case "Status":
				return JWDB_Cache_Status::OnDirty($dbRow);
				break;
			case "Follower":
				return JWDB_Cache_Follower::OnDirty($dbRow);
				break;
			case "Tag":
				return JWDB_Cache_Tag::OnDirty($dbRow);
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

		settype( $idPks, 'array' );
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
		ksort($condition);

		$param_string = var_export($condition, true);
		$param_string = preg_replace("/(=>)\s*'(\d+)'/", "\\1\\2", $param_string);
		$param_string = preg_replace('/[\s]+/','_',$param_string);

		if ( strlen($param_string)>150 )
			$param_string = md5($param_string);
		$mc_key = "TB:$table(" . $param_string . "):$limit";
		return $mc_key;
	}

	/**
	 *	虽然里面用到了 serilize，但是绝对不要把大量的参数当作 $param 传入。比如一个 1000 个元素的数组，这是不对的。
	 *	尽量只传进来关键的元素，比如 $idUser。绝对不要传进来 offset / limit 数据。
	 */
	static public function GetCacheKeyByFunction($function, $param=null)
	{
		if ( is_array($param) )
			ksort($param);

		$param_string = var_export($param, true);
		$param_string = preg_replace("/(=>)\s*'(\d+)'/", "\\1\\2", $param_string);
		$param_string = preg_replace('/[\s]+/','_',$param_string);

		// memcache key 最大 250，留出 100 给其他字串
		if ( strlen($param_string)>150 )
			$param_string = md5($param_string);

		$mc_key = "FN:$function[0]::$function[1](". $param_string. ")";
		return $mc_key;
	}

	/*
	 * @return bool
			succ / fail
	 */
	static public function UpdateTableRowNumber( $table, $idPk, $column, $value=1, $reset=false)
	{
		self::Instance();

		$db_row	= self::GetTableRow( $table, array('id'=>$idPk) );
		$ret 	= JWDB::UpdateTableRowNumber($table, $idPk, $column, $value, $reset);

		// 更新老数据
		self::OnDirty($db_row, $table);

		// 更新新数据
		$db_row 	=  JWDB::GetTableRow($table, array('id'=>$idPk));
		self::OnDirty($db_row, $table);

		return $ret;
	}

	/**
	 * @return new array
	 * return order array by key order array
	 */
	static function SortArrayByKeyOrder( $unsorted = array(), $keys = array() )
	{
		$rtn_array = array();
		foreach( $keys as $key )
		{
			if( isset($unsorted[ $key ] ) )
				$rtn_array[ $key ] = $unsorted[ $key ];
		}
		return $rtn_array;
	}
}
?>
