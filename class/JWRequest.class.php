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
		if ($_SERVER["HTTP_X_FORWARDED_FOR"]) 
		{
  			if ($_SERVER["HTTP_CLIENT_IP"]) 
  				$proxy = $_SERVER["HTTP_CLIENT_IP"];
			 else
  				$proxy = $_SERVER["REMOTE_ADDR"];

			return $proxy;
		}

		return null;
	}


	static public function GetClientIp()
	{
		if ($_SERVER["HTTP_X_FORWARDED_FOR"]) 
		{
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else
		{
			if ($_SERVER["HTTP_CLIENT_IP"])
  				$ip = $_SERVER["HTTP_CLIENT_IP"];
			else
  				$ip = $_SERVER["REMOTE_ADDR"];
		}

		return $ip;
	}

}
?>
