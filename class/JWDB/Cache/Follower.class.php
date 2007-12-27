<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de JWDBCacheFollower Class
 */
class JWDB_Cache_Follower implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDBCacheFollower
	 */
	static private $msInstance = null;

	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWDBCacheFollower
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
		self::Instance();

		$pk_id = $dbRow['id'];
		$user_id= $dbRow['idUser'];
		$follower_user_id= $dbRow['idFollower'];

		$dirty_keys = array();

		array_push( $dirty_keys,
			JWDB_Cache::GetCacheKeyById('Follower', $pk_id), 
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetFollowerInfo_Inner'), array($user_id) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetFollowingInfo_Inner'), array($follower_user_id) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetBioFollowingIds'), array($user_id) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetBioFollowingIds'), array($follower_user_id) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetNotificationIds'), array($user_id) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetFollowingNum'), array($follower_user_id) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWFollower','GetFollowerNum'), array($user_id) 
			)
		);

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}

	static public function GetBioFollowingIds($user_id)
	{
		/* call back function & param */
		$ds_function = array('JWFollower','GetBioFollowingIds');
		$ds_param = array($user_id);

		/* param to make memcache key */
		$mc_param = array($user_id);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$result = JWDB_Cache::GetCachedValueByKey(
			$mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
	
		return $result;
	}

	static public function GetNotificationIds($user_id)
	{
		/* call back function & param */
		$ds_function = array('JWFollower','GetNotificationIds');
		$ds_param = array($user_id);

		/* param to make memcache key */
		$mc_param = array($user_id);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$result = JWDB_Cache::GetCachedValueByKey(
			$mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
	
		return $result;
	}

	static public function GetFollowerNum($user_id)
	{
		/* call back function & param */
		$ds_function = array('JWFollower','GetFollowerNum');
		$ds_param = array($user_id);

		/* param to make memcache key */
		$mc_param = array($user_id);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$result = JWDB_Cache::GetCachedValueByKey(
			$mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
	
		return $result;
	}

	static public function GetFollowingNum($user_id)
	{
		/* call back function & param */
		$ds_function = array('JWFollower','GetFollowingNum');
		$ds_param = array($user_id);

		/* param to make memcache key */
		$mc_param = array($user_id);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$result = JWDB_Cache::GetCachedValueByKey(
			$mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
	
		return $result;
	}

	static public function GetFollowingInfos_Inner($follower_user_id, $num=JWFollower::DEFAULT_FOLLOWER_MAX, $start=0)
	{
		$max_num = $start + $num;
		$mc_max_num = JWDB_Cache::GetMaxCacheNum($max_num);

		// call back function & param
		$ds_function = array('JWFollower','GetFollowingInfos_Inner');
		$ds_param = array($follower_user_id,$mc_max_num);

		// param to make memcache key
		$mc_param = array($follower_user_id);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$follow_info = JWDB_Cache::GetCachedValueByKey(
			$mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);

		if ( false == JWDB_Cache::IsCachedCountEnough(count($follow_info['ids']),$max_num) )
		{
			$follow_info = JWDB_Cache::GetCachedValueByKey(
				$mc_key
				,$ds_function
				,$ds_param
				,$expire_time
				,true
			);
		}

		if ( false==empty($follow_info['ids']) )
		{
			$follow_info['ids'] = array_slice($follow_info['ids'], $start, $num);
			$follow_info['user_ids'] = array_slice($follow_info['user_ids'], $start, $num);
		}
		
		return $follow_info;

	}

	static public function GetFollowerInfos_Inner($user_id, $num=JWFollower::DEFAULT_FOLLOWER_MAX, $start=0)
	{
		$max_num = $start + $num;
		$mc_max_num = JWDB_Cache::GetMaxCacheNum($max_num);

		// call back function & param
		$ds_function = array('JWFollower','GetFollowerInfos_Inner');
		$ds_param = array($user_id,$mc_max_num);

		// param to make memcache key
		$mc_param = array($user_id);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$follow_info = JWDB_Cache::GetCachedValueByKey(
			$mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);

		if ( false == JWDB_Cache::IsCachedCountEnough(count($follow_info['ids']),$max_num) )
		{
			$follow_info = JWDB_Cache::GetCachedValueByKey(
				$mc_key
				,$ds_function
				,$ds_param
				,$expire_time
				,true
			);
		}

		if ( false==empty($follow_info['ids']) )
		{
			$follow_info['ids'] = array_slice($follow_info['ids'], $start, $num);
			$follow_info['user_ids'] = array_slice($follow_info['user_ids'], $start, $num);
		}
		
		return $follow_info;

	}

	static public function GetDbRowsByIds($ids)
	{
		$ids = array_unique($ids);
		return JWDB_Cache::GetCachedDbRowsByIds('Follower', $ids, array("JWFollower","GetDbRowsByIds") );
	}

	static public function GetDbRowById($id)
	{
		$follower_db_rows = self::GetDbRowsByIds(array($id));
		
		if ( empty($follower_db_rows) )
			return array();

		return $follower_db_rows[$id];
	}
}
?>
