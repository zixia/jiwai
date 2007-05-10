<?php
/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      zixia@zixia.net
 * @version     $Id$
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
    static public function &instance()
    {
        if (!isset(self::$msInstance)) {
            $class = __CLASS__;
            self::$msInstance = new $class;
        }
        return self::$msInstance;
    }


    function __construct() {
        ini_set('session.use_cookies',1);
        ini_set('session.cookie_path','/');
        ini_set('session.cookie_domain','.jiwai.de');
        //ini_set('session.gc_maxlifetime',);
        session_start();        
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
			case 'InvitationCode':
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
			$html_str = $_SESSION["__JiWai__Info__$infoType"];

			if ( $useOnce )
				unset ($_SESSION["__JiWai__Info__$infoType"]);

			return $html_str;
		}

		return null;
	}
}
?>
