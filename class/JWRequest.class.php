<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Request Class
 */
class JWRequest {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRequest
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWRequest
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


	static public function GetProxyIp()
	{
		if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) 
			return null;

  		if (!empty($_SERVER["HTTP_CLIENT_IP"])) 
  			return $_SERVER["HTTP_CLIENT_IP"];

		if (!empty($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];

		return null;
	}

	static public function GetIpRegister( $type ) {
		switch ( $type ) {
			case 'msn':
				return 1;
			case 'gtalk':
				return 2;
			case 'qq':
				return 3;
			case 'skype':
				return 4;
			case 'sms':
				return 5;
			default:
				return self::GetClientIp();
		}
	}

	static public function GetRemoteIp()
	{
		if (false == empty($_SERVER["HTTP_X_FORWARDED_FOR"])) 
			return $_SERVER["HTTP_X_FORWARDED_FOR"];

		if (false == empty($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];

		return null;
	}

	static public function GetClientIp()
	{
		if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) 
			return $_SERVER["HTTP_X_FORWARDED_FOR"];

		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
  			return $_SERVER["HTTP_CLIENT_IP"];

		if (!empty($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];

		return null;
	}
}
?>
