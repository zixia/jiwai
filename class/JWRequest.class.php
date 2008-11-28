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

	static public function GetIpRegister( $type=null ) {
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
			case 'yahoo':
				return 6;
			case 'aol':
				return 7;
			case 'fetion':
				return 8;
			case 'jabber':
				return 9;
			case 'xiaonei':
				return 10;
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

	static public function IsWapBrowser(){
		$agent = @$_SERVER['HTTP_USER_AGENT'];
		if( null == $agent )
			return  false;

		$preAgent = strToUpper( substr( trim($agent), 0, 4 ) );
		switch ( $preAgent ) {
			case 'NOKI': //Nokia phones and emulators
			case 'ERIC': //Ericsson WAP phones
			case 'WAPI': //Ericsson WAPIDE 2.0
			case 'MC21': //Ericsson MC218
			case 'AUR ': //Ericsson R320
			case 'R380': //Ericsson R380
			case 'UP.B': //UP.Browser
			case 'WinW': //WinWAP Browser
			case 'UPG1': //UP.SDK 4.0
			case 'UPSI': //Another kind of UP.Browser
			case 'QWAP': //Unknown QWAPPER Browser
			case 'JIGS': //Unknown JiGSaw Browser
			case 'JAVA': //Unknown JavaBased Browser
			case 'ALCA': //Unknown Alcatel-BE3 browser
			case 'MITS': //Unknown Mitsubishi browser
			case 'MOT-': //Unknown browser (UP based?)
			case 'MY S': //Unknown Ericsson devkit browser
			case 'WAPJ': //Virtual WAPJAG
			case 'FETC': //Fetchpage.cgi Perl script
			case 'ALAV': //Another unknown UP based browser
			case 'WAPA': //Another unknown browser
				return true;
			default:
				if( false === strpos( strtoupper($agent), 'WAP' ) )
					return false;
				return true;
		}
		return false;
	}

	static public function IsMozilla() {
		$useragent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'gecko') !== FALSE) {
			preg_match("/gecko\/(\d+)/", $useragent, $regs);
			return $regs[1];
		}
		return FALSE;
	}

	static public function IsIE() {
		$useragent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
		return ($useragent && ! self::IsMozilla() ) ;
	}

	static public function IsWindowsLiveBrowser(){
		/**
		 * User-Agent Headers
		 * MSN, MSN Messenger
		 * Windows Messenger, MSMSGS
		 * Windows Live Messenger, Windows Live Messenger
		 */
		$agent = @$_SERVER['HTTP_USER_AGENT'];
		if( null == $agent )
			return  false;
		return (strpos($agent, 'Windows Live Messenger') === false) ? false : true;
	}
}
?>
