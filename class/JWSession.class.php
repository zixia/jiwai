<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  zixia@zixia.net
 * @version	 $Id$
 */

/**
 * JWSession
 */

Class JWSession {
	/**
	 * Instance of this singleton
	 *
	 * @var JWSession
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWSession
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	function __construct() {
		if( defined('CONSOLE') && CONSOLE == true )
			return;
		ini_set('session.use_cookies',1);
		ini_set('session.cookie_path','/');
		if (!empty($_SERVER['HTTP_HOST'])) {
			$domain = '.'.$_SERVER['HTTP_HOST'];
			if (preg_match('/(\.[^\.]+\.[^\.]+)$/', $domain, $m)) $domain = $m[1];
		} else {
			$domain = '.'.$_SERVER['SERVER_NAME'];
			if (preg_match('/(\.[^\.]+\.[^\.]+)$/', $domain, $m)) $domain = $m[1];
		}
		ini_set('session.cookie_domain', $domain);
		//ini_set('session.gc_maxlifetime',);
        if( isset( $_GET['PHPSESSID'] ) ) {
            if( preg_match( '/^[0-9A-Za-z]+$/', $_GET['PHPSESSID'] ) ) {
                session_id( $_GET['PHPSESSID'] );
            }
        }
		session_start();		
	}

	function __destruct() {
		if ( $length = ob_get_length() ) {
			@header("Content-Length: {$length}");
		}
	}


	public static function SetInfo($infoType, $data)
	{
		if ( empty($data) )
			return;

		switch ($infoType)
		{
			case 'error':
				$_SESSION["__JiWai__Info__$infoType"] = $data;
				break;
			case 'notice':
				$_SESSION["__JiWai__Info__$infoType"] = $data;;
				break;
			case 'info':
				$_SESSION["__JiWai__Info__$infoType"] = $data;;
				break;
			case 'reset_password':
				//fall down
			case 'invitation_id':
				$_SESSION["__JiWai__Info__$infoType"] = $data;;
				break;
			case 'inviter_id':
				$_SESSION["__JiWai__Info__$infoType"] = $data;;
				break;
			default:
				throw new JWException('info type not support');
		}
			
			
	}

	/*
	 * @param useOnce: delete info after get if set true.
	 */
	public static function GetInfo($infoType='err', $useOnce=true)
	{
		if ( isset($_SESSION["__JiWai__Info__$infoType"]) )
		{
			$info_str = $_SESSION["__JiWai__Info__$infoType"];

			if ( $useOnce )
				unset ($_SESSION["__JiWai__Info__$infoType"]);

			return $info_str;
		}

		return null;
	}
}
?>
