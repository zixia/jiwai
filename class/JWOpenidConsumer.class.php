<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */


/**
 * Require the OpenID consumer code.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require the "file store" module, which we'll need to store OpenID
 * information.
 */
require_once "Auth/OpenID/FileStore.php";

/**
 * JiWai.de Openid Class
 */
class JWOpenidConsumer 
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWOpenidConsumer
	 */
	static private $msInstance;

	static private $msConsumer;

	const URL_TRUST_ROOT 	= 'http://jiwai.de';
	const URL_FINISH_AUTH 	= 'http://jiwai.de/wo/openid/finish_auth';

	/**
	 * Instance of this singleton class
	 *
	 * @return JWOpenidConsumer
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
		$store_path = "/tmp/_php_consumer_test";

		if (!file_exists($store_path) &&
    			!mkdir($store_path)) 
		{
    		throw new JWException( "Could not create the FileStore directory '$store_path'. "
									.  " Please check the effective permissions." );
		}

		$store = new Auth_OpenID_FileStore($store_path);

		/**
 		 * Create a consumer object using the store object created earlier.
 		 */
		self::$msConsumer = new Auth_OpenID_Consumer($store);

	}

	static public function AuthRedirect($urlOpenid)
	{
		self::Instance();

		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') 
		{
    		$scheme .= 's';
		}

		// Begin the OpenID authentication process.
		$auth_request = self::$msConsumer->begin($urlOpenid);

		// Handle failure status return values.
		if (!$auth_request) 
		{
    		//$error = "Authentication error.";
			return false;
		}

		$auth_request->addExtensionArg('sreg', 'optional', 'email');

		// Redirect the user to the OpenID server for authentication.  Store
		// the token for this authentication so we can verify the response.

		$redirect_url = $auth_request->redirectURL(	 JWOpenidConsumer::URL_TRUST_ROOT
													,JWOpenidConsumer::URL_FINISH_AUTH
										);

		header("Location: ".$redirect_url);
		return exit(0);
	}

	static public function GetCompleteResponse($get)
	{
		self::Instance();
		return self::$msConsumer->complete($get);
	}
}
?>
