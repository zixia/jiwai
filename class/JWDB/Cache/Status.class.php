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

		$dirty_list = array (	 
							 JWDB_Cache::GetCacheKeyById		('Status'	, $pk_id)

							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusIdsFromReplies'		,$reply_to_user_id)
							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusIdsFromSelfNReplies',$reply_to_user_id)
							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusIdsFromSelfNReplies',$user_id)

							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusIdsFromUser'	, $user_id)

							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusNum'			, $user_id)
							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusNumFromReplies'	, $user_id)

							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusNumFromSelfNReplies',$user_id)
							,JWDB_Cache::GetCacheKeyByFunction	('Status'	, 'GetStatusNumFromSelfNReplies',$reply_to_user_id)
						);
/*
							"Status(id=$pk_id)"

								,"Status[GetStatusIdsFromUser($user_id)]"

								,"Status[GetStatusIdsFromSelfNReplies($reply_to_user_id)]"
								,"Status[GetStatusIdsFromReplies($reply_to_user_id)]"
								,"Status[GetStatusNumFromReplies($reply_to_user_id)]"
								,"Status[GetStatusNumFromSelfNReplies($reply_to_user_id)]"

								,"Status[GetStatusNum($user_id,$reply_to_user_id)]" 
							);
					*/

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}


	/**
	 *	FIXME: not support idSince & $timeScince param.
	 */
	static public function GetStatusIdsFromUser($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_offset		= $start + $num;
		$mc_max_offset	= JWDB_Cache::GetMaxOffset($max_offset);

		$mc_key 	= JWDBCache::GetCacheKeyByFunction	('Status','GetStatusIdsFromUser',$user_id);

		/*
		 *	对于过多的结果集，只保留一段时间
		 */
		if ( $mc_max_offset > JWDB_Cache::NUM_LARGE_DATASET )
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS_LARGE_DATA;
		else
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$status_ids	= JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusIdsFromUser')
									,array($idUser,$mc_max_offset)
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
										,array('JWStatus','GetStatusIdsFromUser')
										,array($idUser,$mc_max_offset)
										,$expire_time
										,true
									);
		}

		return array_slice($status_ids,$start,$num);
	}


	/**
	 *	FIXME: not support idSince & $timeScince param.
	 */
	static public function GetStatusIdsFromFriends($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_offset		= $start + $num;
		$mc_max_offset	= JWDB_Cache::GetMaxOffset($max_offset);

		$mc_key 	= JWDBCache::GetCacheKeyByFunction	('Status','GetStatusIdsFromFriends',$user_id);


		/**
		 *	这个无法通过逻辑过期（性能问题），但是对实时性要求不高，所以 cache 1 min
		 */
		$expire_time	= 60;

		$status_ids	= JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusIdsFromUser')
									,array($idUser,$mc_max_offset)
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
										,array('JWStatus','GetStatusIdsFromUser')
										,array($idUser,$mc_max_offset)
										,$expire_time
										,true
									);
		}

		return array_slice($status_ids,$start,$num);
	}
	

	
	static public function GetStatusIdsFromReplies($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_offset		= $start + $num;
		$mc_max_offset	= JWDB_Cache::GetMaxOffset($max_offset);

		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction('Status', 'GetStatusIdsFromReplies', $idUser);
		
		
		/*
		 *	对于过多的结果集，只保留一段时间
		 */
		if ( $mc_max_offset > JWDB_Cache::NUM_LARGE_DATASET )
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS_LARGE_DATA;
		else
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$status_ids	= JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusIdsFromReplies')
									,array($idUser,$mc_max_offset)
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
		return JWDB_Cache::GetCachedDbRowsByIds('Status', $idStatuses, array("JWStatus","GetStatusDbRowsByIds") );
	}

	static public function GetStatusDbRowById ($idStatus)
	{
		$status_db_rows = self::GetStatusDbRowsByIds(array($idStatus));

		if ( empty($status_db_rows) )
			return array();

		return $status_db_rows[$idStatus];
	}

	static public function GetStatusNum($idUser)
	{
		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction('Status', 'GetStatusNum', $idUser);
		
		$expire_time	= JWStatus::PERMANENT_EXPIRE_SECENDS;

		return JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusNum')
									,array($idUser)
									,$expire_time
								);
	}

	static public function GetStatusNumFromReplies($idUser)
	{
		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction('Status', 'GetStatusNumFromReplies', $idUser);
		
		$expire_time	= JWStatus::PERMANENT_EXPIRE_SECENDS;

		return JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusNumFromReplies')
									,array($idUser)
									,$expire_time
								);
	}


	static public function GetStatusNumFromSelfNReplies($idUser)
	{
		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction('Status', 'GetStatusNumFromSelfNReplies', $idUser);
		
		$expire_time	= JWStatus::PERMANENT_EXPIRE_SECENDS;

		return JWDB_Cache::GetCachedValue(
									 $mc_key
									,array('JWStatus','GetStatusNumFromSelfNReplies')
									,array($idUser)
									,$expire_time
								);
	}
}
?>
