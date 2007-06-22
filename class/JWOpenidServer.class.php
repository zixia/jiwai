<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

require_once "Auth/OpenID.php";    
require_once "Auth/OpenID/Server.php";

/**
 * JiWai.de Openid Class
 */
class JWOpenidServer {
	/**
	 * Instance of this singleton
	 *
	 * @var JWOpenidServer
	 */
	static private $msInstance;
	static private $msServer;

	const URL_SERVER	= 'http://jiwai.de/wo/openid/server';

	/**
	 * Instance of this singleton class
	 *
	 * @return JWOpenidServer
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
		$store = JWOpenidServer::GetOpenIdStore();
        self::$msServer =& new Auth_OpenID_Server($store);
	}


	private function GetOpenIdStore()
	{
    	require_once "Auth/OpenID/FileStore.php";
		return new Auth_OpenID_FileStore("/tmp/open_id_server");
	}


	static function GetServer()                                                                                 
	{                                                                                                    
		self::Instance();

    	return self::$msServer;                                                                                  
	}    

	static function  GetServerUrl()
	{
		return JWOpenidServer::URL_SERVER;
	}

	/*
	 *	 将 openid 的 request info 存起来备用
	 */
	static function SetRequestInfo($info=null)
	{
    	if (!isset($info)) {
       		unset($_SESSION['openid_request']);
    	} else {
        	$_SESSION['openid_request'] = serialize($info);
    	}
	}

	/*
	 *	获取 openid 的 requiest info
	 */
	static function GetRequestInfo()
	{
		return isset($_SESSION['openid_request'])
        	? unserialize($_SESSION['openid_request'])
        	: false;
	}

	static function DoAuth($info)
	{
    	if (!$info) 
		{
        	// There is no authentication information, so bail
        	return self::AuthCancel(null);
    	}

    	$req_url = $info->identity;

        self::SetRequestInfo();

        $server 		= self::GetServer();
        $response 		= $info->answer(true);

        $webresponse 	= $server->encodeResponse($response);

        $new_headers = array();

        foreach ($webresponse->headers as $k => $v) {
            $new_headers[] = $k.": ".$v;
        }

        //return array($new_headers, $webresponse->body);
    	array_walk($new_headers, 'header');
    	header("Connection: close");
    	print $webresponse->body;
		exit(0);

	}


	static function AuthCancel($info=null)
	{
    	if ($info) {
        	self::SetRequestInfo();
        	$url = $info->getCancelURL();
    	} else {
        	$url = self::GetServerUrl();
    	}
    	header("Location: $url");
    	header("Connection: close");
		exit(0);
	}


	static public function GetSregByUserId($idUser)
	{
		$user_db_row = JWUser::GetUserInfo($idUser);

		if ( empty($user_db_row) )
			return null;

    	return array ('email'=>$user_db_row['email']);
	}
}
?>
