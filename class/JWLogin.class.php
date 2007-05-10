<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Login Class
 */
class JWLogin {
	/**
	 * Instance of this singleton
	 *
	 * @var JWLogin
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWLogin
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


	static public function Login( $userIdOrName, $isRememberMe=true )
	{
		if ( preg_match("/^\d/",$userIdOrName) ){
			$idUser 	= $userIdOrName;
		}else{
			$user_info 	= self::GetUserInfoByName($userIdOrName);
			$idUser		= $user_info['id'];
		}

		$_SESSION['idUser'] = $idUser;

		if ( $isRememberMe )
			self::SetRememberUser();
		else
			self::ForgetRemembedUser();

		return true;
	}


	static public function Logout()
	{
		self::ForgetRemembedUser();
		unset ($_SESSION['idUser']);
	}


	static public function MustLogined()
	{
		if ( self::IsLogined() ){
			return true;
		}

		$_SESSION['login_redirect_url'] = $_SERVER['SCRIPT_URI'];

		header ("Location: /wo/login"); 
		exit(0);
	}


	/*
	 * 检查是否已经登录，或者是系统记住的登录用户
	 * @return true / false
	 */
	static public function IsLogined()
	{
		if ( array_key_exists('idUser',$_SESSION) )
			return true;

		$idUser = self::GetRememberUser();

		if ( isset($idUser) )
		{
			$_SESSION['idUser'] = $idUser;
			return true;
		}
		
		return false;
	}


	static public function GetCurrentUserId()
	{
		if ( JWLogin::IsLogined() )
			return intval($_SESSION['idUser']);

		return null;
	}



	/*
	 * 检查客户端cookie，并与 remember me 的密钥做比对。如果符合，则得到 idUser
	 * @return $idUser or null;
	 * 
	 */
	static function GetRememberUser()
	{
		$idUser = @$_COOKIE['JiWai_de_remembered_user_id'];
		$secret = @$_COOKIE['JiWai_de_remembered_user_code'];

		if ( empty($secret) || empty($idUser) )
			return null;
		

		if ( ! self::LoadRememberMe($idUser,$secret) )
		{
			setcookie('JiWai_de_remembered_user_id',	'', time()-3600, '/');
			setcookie('JiWai_de_remembered_user_code',	'', time()-3600, '/');
			return null;
		}

		// refresh browser cookie lifetime
		self::RefreshRememberUser();

		return intval($idUser);
	}


	/*
	 * @description refresh browser cookie lifetime.
	 * @return 		bool 
	 */
	static function RefreshRememberUser()
	{
		$id_user	= @$_COOKIE['JiWai_de_remembered_user_id'];
		$secret		= @$_COOKIE['JiWai_de_remembered_user_code'];

		if ( empty($secret) || empty($id_user) )
			return false;
	
		setcookie('JiWai_de_remembered_user_id', 	$id_user, time() + 31536000	, '/');
		setcookie('JiWai_de_remembered_user_code',	$secret	, time() + 31536000	, '/');

		return true;
	}


	/*
	 *	设置  rememeber me 的 cookie 信息
	 * 	@return bool 
	 */
	static function SetRememberUser()
	{
		$id_user = self::GetCurrentUserId();

		if ( empty($id_user) )
			return false;
			
		$secret = JWDevice::GenSecret(16);

		if ( ! self::SaveRememberMe($id_user,$secret) )
		{
			setcookie('JiWai_de_remembered_user_id'		, '' , time()-3600	, '/');
			setcookie('JiWai_de_remembered_user_code'	, '' , time()-3600	, '/');
			return false;
		}

		setcookie('JiWai_de_remembered_user_id', 	$id_user, time() + 31536000	, '/');
		setcookie('JiWai_de_remembered_user_code',	$secret	, time() + 31536000	, '/');
		
		return true;
	}


	/*
	 *	删除 cookie 信息 
	 * 	@return bool
	 */
	static function ForgetRemembedUser()
	{

		$id_user = @$_COOKIE['JiWai_de_remembered_user_id'];
		$secret = @$_COOKIE['JiWai_de_remembered_user_code'];

		setcookie('JiWai_de_remembered_user_id'		, '', time()-3600, '/');
		setcookie('JiWai_de_remembered_user_code'	, '', time()-3600, '/');
		
		if ( isset($id_user) || isset($secret) )
			self::DelRememberMe($id_user,$secret);


		return true;
	}

	/*
	 * @return bool
	 */
	static function SaveRememberMe($idUser, $secret)
	{
		
		if ( empty($idUser) || empty($secret) || (!is_numeric($idUser)) )
			return false;

		return JWDB::SaveTableRow('RememberMe', array (	'idUser'	=>	intval($idUser)
													, 'secret'	=>	$secret
											) );
	}


	/*
	 *	检查数据库中是否存在 remember 信息
	 * @return bool
	 */
	static function LoadRememberMe($idUser, $secret)
	{
		if ( empty($idUser) || empty($secret) || (!is_numeric($idUser)) )
			return false;
		

		return JWDB::ExistTableRow('RememberMe', array (	'idUser'	=> intval($idUser)
													, 'secret'	=> $secret
												)
						);
	}


	/*
	 *	删除对应 $secret 的 $idUser 的 remember me 记录
	 *	@param	$secret		客户端 remember 的密钥
	 * 	@return int
			deleted row num
	 */
	static function DelRememberMe($idUser, $secret)
	{
		if ( empty($idUser) || empty($secret) || (!is_numeric($idUser)) )
			return true;
		
		return JWDB::DelTableRow('RememberMe', array (	'idUser'	=> intval($idUser)
														, 'secret'	=> $secret
												) );
	}



}
?>
