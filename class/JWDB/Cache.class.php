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


	/*
	 *	降低并发访问数据库的 Cache 时间
	 *
	 *	对于同样的数据库请求，我们在 MIN_EXPIRE_SECENDS 中，只获取一次
	 *	这样设计的假设前提：我们现在假设秒级的 cache 时间不会带来实时性数据问题
	 *
	 *	目前设置： 1s
	 */
	const TEMPORARY_EXPIRE_SECENDS = 1;

	/*
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
		$this->msMemcache = JWMemcache::Instance();
	}

	/**
	* Destructing method, write everything left
	*
	*/
	function __destruct()
	{
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
	static function GetCachedValue($mcKey, $function, $param, $timeExpire=self::TEMPORARY_EXPIRE_SECENDS, $forceReload=false)
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

		$mc_key = JWMemcache::GetKeyFromFunction("JWDB::GetQueryResult", array($sql,$moreThanOne));

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
			$key 	= self::$msMemcache->GetKeyFromPk($table, $condition['id']);

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
		return JWDB::GetTableRow($table,$condition,$limit);
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
}
?>
