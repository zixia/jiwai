<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de JWDBCacheStatus Class
 */
class JWDB_Cache_Status implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDBCacheStatus
	 */
	static private $msInstance = null;

	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWDBCacheStatus
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


	static function OnDirty($dbRow, $table=null)
	{
		/* 	取出 idPK 的 row，
		 *	然后依据自己表中的cache逻辑，得到相关其他应该 OnDirty 的 key
		 *	接下来一个一个的 OnDirty 过去
		 */ 

		$pk_id				= $dbRow['id'];
		$user_id			= $dbRow['idUser'];
		$reply_to_user_id	= $dbRow['idUserReplyTo'];

		$dirty_keys = array (	 "Status(id=$pk_id)"

								,"Status[GetStatusIdsFromUser($user_id)]"

								,"Status[GetStatusIdsFromSelfNReplies($reply_to_user_id)]"
								,"Status[GetStatusIdsFromReplies($reply_to_user_id)]"
								,"Status[GetStatusNumFromReplies($reply_to_user_id)]"
								,"Status[GetStatusNumFromSelfNReplies($reply_to_user_id)]"

								,"Status[GetStatusNum($user_id,$reply_to_user_id)]" 
							);

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}

	
	static public function GetStatusIdsFromReplies($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_offset		= $start + $num;
		$mc_max_offset	= JWDB_Cache::GetMaxOffset($max_offset);

		$mc_key 	= JWDB_Cache::GetKeyFromFunction('Status', 'GetStatusIdsFromReplies', $idUser);
		
		
		/*
		 *	对于过多的结果集，只保留一段时间
		 */
		if ( $mc_max_offset > 1000 )
			$expire_time = 3600;
		else
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$status_ids	= JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusIdsFromReplies')
									,array($idUser)
									,$expire_time
								);

		/**
		 *	存在可能：由于以前 cache 了 $num 较小数字时候的返回结果，导致直接取回了数量不足的结果集
		 *	所以对结果集的总数进行比对，如果数据不足，则删除数据，重新进行数据库查询
		 *
		 *	比如：1分钟前，有一次 GetStatusIdsFromReplies($num=100,$start=0)，则会 cache 住前 100 条数据
					现在，我们调用 GetStatusIdsFromReplies($num=20,$start=100)，则会获取到刚刚 cache 的 100 条数据，不足
					所以需要重新进行数据库查询，获取足够的数据：增加 $forceReload=true 的参数
		 *
		 */

		if ( count($status_ids)<$max_offset)
		{
			$status_ids	= JWDB_Cache::GetCachedValue(
										 $mc_key
										,array('JWStatus','GetStatusIdsFromReplies')
										,array($idUser,$mc_max_offset)
										,$expire_time
										,true
									);
		}

		return array_slice($status_ids,$start,$num);
	}

	static public function GetStatusDbRowsByIds ($idStatuses)
	{
		return self::GetDbRowsByIds('Status', $idStatuses, "JWStatus", "GetStatusDbRowsByIds");
		/*
		 *	Stage 1. 根据 ids 获取 memcache 的 keys
		 */
		$mc_keys 	= JWMemcache::DbKeys2McKeys('Status', $idStatuses);

		/*
		 *	Stage 1.1 通过 memcache 尝试获取数据
		 */
		$hit_mc_rows = self::$msMemcache->Get($mc_keys);
		$hit_mc_keys = array_keys($hit_mc_rows);

		$hit_status_ids = JWMemcache::McKeys2DbKeys('Status', $hit_mc_keys);

		/*
		 *	Stage 1.2 将 memcache 返回数据中的 memcache key 转化为 db key
		 */
		$hit_db_rows = array();
		foreach ( $hit_status_ids as $hit_status_id )
		{
			$hit_db_rows[$hit_status_id] = $hit_mc_rows[JWMemcache::DbKey2McKey($hit_status_id)];
		}

		/*
		 *	Stage 1.3 如果 memcache 返回了所有数据，则完全命中，返回数据后结束。
		 */
		if ( count($hit_status_ids)==count($idStatuses) )
		{
			return $hit_db_rows;
		}



		/*
		 *	Stage 2. memcache 没有完全命中（或没有命中），我们需要从数据库中获取数据，先算出哪些 id 没有命中 cache
		 */
		$unhit_status_ids	= array_diff($idStatuses, $hit_status_ids);


		/*
		 *	Stage 2.1 准备开始获取数据。为了避免同一台服务器上多个进程同时获取memcache数据，我们在进入之前设置一个 mutex 
		 */
		$mutex = new JWMutex($mc_keys);
		// TODO finish mutex & array merge here.

		$mutex->Acquire();

		/*
		 *	Stage 2.2 成功获取 Mutex，这时候再次检查刚刚 unhit 的数据是否现在已经有了。因为在获取 Mutex 的时候，可能有其他进程已经将数据进行设置了。
		 */
		$retry_mc_keys 		= JWMemcache::DbKeys2McKeys('Status', $unhit_status_ids);
		$retry_hit_mc_rows 	= self::$msMemcache->Get($retry_mc_keys);
		$retry_hit_mc_keys	= array_keys($retry_hit_mc_rows);

		$retry_hit_db_rows	= array();
		foreach ( $unhit_status_ids as $unhit_status_id )
		{
			$retry_hit_db_rows[$unhit_status_id] = $retry_hit_mc_rows[JWMemcache::DbKey2McKey($unhit_status_id)];
		}

		/*
		 *	Stage 2.3 如果得到了完整数据，则释放 Mutex，返回数据
		 */
		if ( count($retry_hit_mc_keys)==count($retry_mc_keys) )
		{
			$mutex->Release();
			return array_assoc_merge($hit_mc_rows, $retry_hit_db_rows);
		}
	

		/*
		 *	Stage 2.4 仍然没有完整数据，则进行数据库查询 TODO 刚刚有可能能得到几个数据，可以再次 diff 提高性能
		 */
		$db_rows		= JWStatus::GetStatusDbRowsByIds($unhit_status_ids);
		
		$unhit_mc_keys	= JWMemcache::DbKeys2McKeys('Status', array_keys($db_rows));

		foreach ( $unhit_mc_keys as $unhit_mc_key )
		{
			self::$msMemcache->Set($unhit_mc_key, $db_rows[JWMemcache::DbKey2McKey($status_id)]);
		}

		/*
		 *	Stage 2.5 释放 Mutex，返回完整数据
		 */
		$mutex->Release();

		return array_assoc_merge($hit_mc_rows, $db_rows);
	}
}
?>
