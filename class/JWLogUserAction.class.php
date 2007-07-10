<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de LogUserAction Class
 */
class JWLogUserAction {
	/**
	 * Instance of this singleton
	 *
	 * @var JWLogUserAction
	 */
	static private $msInstance;

	static private $msClientIp;
	static private $msProxyIp;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWLogUserAction
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
		self::$msClientIp 	= JWRequest::GetClientIp();
		self::$msProxyIp 	= JWRequest::GetProxyIp();
	}

	static public function OnLogin($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow('LogUserAction', array(	 'action'	=> 'login'
															,'idUser'	=> $idUser
															,'ip'		=> JWDB::MysqlFuncion_Aton($ip)
															,'proxy'	=> JWDB::MysqlFuncion_Aton($proxy)
														)
									);
	}

	static public function OnRememberLogin($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow( 'LogUserAction',	array(	 'action'	=> 'rememberlogin'
															,'idUser'	=> $idUser
															,'ip'		=> JWDB::MysqlFuncion_Aton($ip)
															,'proxy'		=> JWDB::MysqlFuncion_Aton($proxy)
														)
									);
	}


	static public function OnLogout($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow( 'LogUserAction', array(	 'action'	=> 'logout'
															,'idUser'	=> $idUser
															,'ip'		=> JWDB::MysqlFuncion_Aton($ip)
															,'proxy'		=> JWDB::MysqlFuncion_Aton($proxy)
														)
									);
	}

	static public function OnRememberMe($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow( 'LogUserAction', array(	 'action'	=> 'rememberme'
															,'idUser'	=> $idUser
															,'ip'		=> JWDB::MysqlFuncion_Aton($ip)
															,'proxy'		=> JWDB::MysqlFuncion_Aton($proxy)
														)
									);
	}


	static public function OnForgetMe($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow( 'LogUserAction', array(	 'action'	=> 'forgetme'
															,'idUser'	=> $idUser
															,'ip'		=> JWDB::MysqlFuncion_Aton($ip)
															,'proxy'	=> JWDB::MysqlFuncion_Aton($proxy)
														)
									);
	}


}
?>
