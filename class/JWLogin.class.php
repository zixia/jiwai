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
			$user_info 	= JWUser::GetUserInfo($userIdOrName);
			$idUser		= $user_info['id'];
		}

		$_SESSION['idUser'] = $idUser;

		JWLogUserAction::OnLogin($idUser);

		if ( $isRememberMe )
			self::SetRememberUser();
		else
			self::ForgetRemembedUser();

		return true;
	}


	static public function Logout()
	{
		if ( ! JWLogin::IsLogined() )
			return;

		$user_id = $_SESSION['idUser'];

		self::ForgetRemembedUser();
		unset ($_SESSION['idUser']);

		JWLogUserAction::OnLogout($user_id);
	}


	static public function MustLogined($allow_drift=false)
	{
		if ( self::IsLogined() )
		{
			if ( false===$allow_drift )
			{
				$current_user_info = JWUser::GetCurrentUserInfo();
				if ( 'ANONYMOUS' !== $current_user_info['srcRegister'] )
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		}

		if ( true==$allow_drift && $possible_user_id = self::GetPossibleUserId() )
		{
			self::Login($possible_user_id);
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
			JWLogUserAction::OnRememberLogin($idUser);
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


	static public function GetPossibleUserId()
	{
		$idUser = self::GetCurrentUserId();
		if( $idUser )
			return $idUser;

		$ip = JWRequest::GetRemoteIp();

		if ( null == $ip )
			return null;

		$ip_list = preg_split('/[\s,]+/', $ip);
		if (empty($ip_list) )
			return null;

		$ip = array_pop( $ip_list );
		
		/** Tempory name **/
		$ipName = preg_replace( '/(\d+)$/', '*', $ip );
		$ipFullName = $ipName;

		/**
		$ipName = 'Anonymity';
		$ipFullName = '匿名';
		*/

		$userInfo = JWUser::GetUserInfo( $ipName );
		if( $userInfo )
			return $userInfo['id'];
	
		return JWUser::CreateDriftBottle($ipName, $ipFullName);
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
		

		if ( null==self::LoadRememberMe($secret) )
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
		$user_id = self::GetCurrentUserId();

		if ( empty($user_id) )
			return false;
			
		$secret = JWDevice::GenSecret(32,JWDevice::CHAR_ALL);

		if ( ! self::SaveRememberMe($user_id,$secret) )
		{
			setcookie('JiWai_de_remembered_user_id'		, '' , time()-3600	, '/');
			setcookie('JiWai_de_remembered_user_code'	, '' , time()-3600	, '/');
			return false;
		}

		setcookie('JiWai_de_remembered_user_id', 	$user_id, time() + 31536000	, '/');
		setcookie('JiWai_de_remembered_user_code',	$secret	, time() + 31536000	, '/');
		
		JWLogUserAction::OnRememberMe($user_id);

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
		
		$idUser = JWDB::CheckInt($idUser);

		if ( empty($secret) )
			return false;

		return JWDB::SaveTableRow('RememberMe', array (	'idUser'	=>	intval($idUser)
													, 'secret'	=>	$secret
											) );
	}


	/*
	 *	检查数据库中是否存在 remember 信息
	 * @return int	$idUser，没有则返回 null
	 */
	static function LoadRememberMe($secret)
	{
		if ( empty($secret) )
			return false;
		
		$row = JWDB::GetTableRow('RememberMe', array('secret'=>$secret));

		if ( empty($row) )
			return null;

		return $row['idUser'];
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
		
		JWLogUserAction::OnForgetMe($idUser);

		return JWDB::DelTableRow('RememberMe', array (	'idUser'	=> intval($idUser)
														, 'secret'	=> $secret
												) );
	}


	/*
	 *	重定向到登录页面，同时设定返回url地址
	 */
	static function RedirectToLogin($urlBack='/')
	{
		$_SESSION['login_redirect_url'] = $urlBack;
		header("Location: /wo/login");
		exit(0);
	}
}
?>
