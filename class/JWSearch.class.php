<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Search Class
 */
class JWSearch {
	/**
	 * Instance of this singleton
	 *
	 * @var JWSearch
	 */
	static private $msInstance = null;

	/**
	 * Search TYPE
	 */
	const SEARCH_NAME = 1;
	const SEARCH_EMAIL = 2;
	const SEARCH_MOBILE = 3;
	const SEARCH_QQ = 4;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWSearch
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
	}

	/**
	 *
	 */
	static function GetSearchUserIds($q,$limit=100,$offset=0){
		$q = strtolower( trim($q) );
		$searchType = self::GetSearchObjectType($q);
		switch($searchType){
			case self::SEARCH_EMAIL:
				return JWUser::GetSearchEmailUserIds($q,$limit,$offset);
			case self::SEARCH_NAME:
				return JWUser::GetSearchNameUserIds($q,$limit,$offset);
			case self::SEARCH_MOBILE:
				return JWUser::GetSearchDeviceUserIds($q,array('sms'),$limit,$offset);
			case self::SEARCH_QQ:
				return JWUser::GetSearchDeviceUserIds($q,array('qq'),$limit,$offset);
		}
		return array();
	}

	/**
	 * Get Search type
	 * @param string @key
	 * @return int searchType
	 */
	static function GetSearchObjectType($key){
		if( false !== strpos($key, '@') ) {
			return self::SEARCH_EMAIL;
		}
		if( is_numeric( $key ) ){
			if( strlen($key)==11 && ( 0 === strpos($key, '13' ) || 0 === strpos($key, '15')) ){
				return self::SEARCH_MOBILE;
			}
			return self::SEARCH_QQ;
		}		
		return self::SEARCH_NAME;
	}
}
?>
