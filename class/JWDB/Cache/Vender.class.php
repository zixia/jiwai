<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de Vender Class
 */
class JWDB_Cache_Vender implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB_Cache_Vender
	 */

	static $msInstance;


	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWVender
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

	static public function Query( $user_id , $vender ) {
		$mc_key = JWDB_Cache::GetCacheKeyByFunction(array('JWVender', 'Query'), array($user_id, $vender));
		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;
		$r = JWDB_Cache::GetCachedValueByKey($mc_key, array('JWVender', 'Query'), array($user_id, $vender), $expire_time);
		return $r;
	}
	
	static function _dirty( $user_id , $vender ) {
		$mc_key = JWDB_Cache::GetCacheKeyByFunction(array('JWVender', 'Query'), array($user_id, $vender));
		 self::Instance()->Del( $mc_key );
		
	}


	static function OnDirty($v, $t=null) {
	}
}
