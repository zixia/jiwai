<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de JWDBCacheUser Class
 */
class JWDB_Cache_User implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDBCacheUser
	 */
	static private $msInstance = null;

	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWDBCacheUser
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
		/* 	取出 idPK 的 row，
		 *	然后依据自己表中的cache逻辑，得到相关其他应该 OnDirty 的 key
		 *	接下来一个一个的 OnDirty 过去
		 */ 

		$pk_id = $dbRow['id'];
		$email = strrev($dbRow['email']);
		$name_screen = $dbRow['nameScreen'];
		$name_url = $dbRow['nameUrl'];

		$dirty_keys = array();

		array_push( $dirty_keys,
			JWDB_Cache::GetCacheKeyById('User', $pk_id), 
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWUser','GetDbRowByNameScreen'), array($name_screen) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWUser','GetDbRowByNameUrl'), array($name_url) 
			),
			JWDB_Cache::GetCacheKeyByFunction( 
				array('JWUser','GetDbRowByEmail'), array($email) 
			)
		);

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}

	static public function GetDbRowByNameScreen($name_screen)
	{
		$ds_function = array('JWUser','GetDbRowByNameScreen');
		$ds_param = array($name_screen);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$user_info = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);

		return $user_info;
	}

	static public function GetDbRowByNameUrl($name_url)
	{
		$ds_function = array('JWUser','GetDbRowByNameUrl');
		$ds_param = array($name_url);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$user_info = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);

		return $user_info;
	}

	static public function GetDbRowByEmail($email)
	{
		$ds_function = array('JWUser','GetDbRowByEmail');
		$ds_param = array($email);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$user_info = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);

		return $user_info;
	}

	static public function GetDbRowsByIds($user_ids)
	{
		$user_ids = array_unique($user_ids);
		return JWDB_Cache::GetCachedDbRowsByIds('User', $user_ids, array("JWUser","GetDbRowsByIds") );
	}

	static public function GetDbRowById($user_id)
	{
		$user_db_rows = self::GetDbRowsByIds(array($user_id));
		
		if ( empty($user_db_rows) )
			return array();

		return $user_db_rows[$user_id];
	}
}
?>
