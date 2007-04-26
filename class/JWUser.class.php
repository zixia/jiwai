<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de User Class
 */
class JWUser {
	/**
	 * Instance of this singleton
	 *
	 * @var JWUser
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWUser
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	static public function logout()
	{
		self::ForgetRemembedUser();
		unset ($_SESSION['idUser']);
	}


	static public function Login( $name_or_email, $pass, $isRememberMe=true )
	{
		$db = JWDB::get_db();

		$idUser = null;

		$name_or_email	= $db->escape_string($name_or_email);
		$pass 			= $db->escape_string($pass);


		#
		# Step 1. get idUser & pass(md5) from DB
		#
		if ( strpos($name_or_email,'@') ){
			$sql = <<<_SQL_
SELECT	id as idUser, pass 
FROM	User 
WHERE	email=REVERSE('$name_or_email')
_SQL_;
		}else{ // nameScreen
			$sql = <<<_SQL_
SELECT	id as idUser, pass 
FROM 	User 
WHERE 	nameScreen='$name_or_email'
_SQL_;
		}

		$arr = JWDB::GetQueryResult($sql);

		if ( ! $arr )
			return false;

		$idUser = $arr['idUser'];
		$db_pass = $arr['pass'];

		#
		# Step 2. 检查密码是否匹配
		#
		if ( ! self::VerifyPassword($pass, $idUser) )
			return false;


		$_SESSION['idUser'] = $idUser;

		if ( $isRememberMe )
			self::SetRememberUser();
		else
			self::ForgetRemembedUser();

		return true;
	}

	/*
	 * @param string
	 * @param int
	 * @return bool
	 */
	static function ChangePassword($plainPassword, $idUser=null)
	{
		// not permit empty pass
		if ( empty($plainPassword) )
			return false;

		if ( null===$idUser )
			$idUser = self::GetCurrentUserId();

		$md5_pass = self::CreatePassword($plainPassword);

		$sql = <<<_SQL_
UPDATE	User
SET		pass='$md5_pass'
WHERE	id=$idUser
_SQL_;
	
		return JWDB::Execute($sql);
	}


	/*
	 * @param string
	 * @return string
	 */
	static function CreatePassword($plainPassword)
	{
		$salt	= '$1$' . JWDevice::GenSecret(8) . '$';
		return	crypt($plainPassword, $salt);
	}


	/*
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	static public function VerifyPassword($password, $idUser=null)
	{
		if ( null===$idUser )
			$idUser = self::GetCurrentUserId();

		$md5_pass = self::GetUserInfoById($idUser,'pass');

		if ( crypt($password,$md5_pass)!=$md5_pass )
			return false;

		return true;
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


	static public function GetCurrentUserId()
	{
		if ( self::IsLogined() )
			return intval($_SESSION['idUser']);

		return null;
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

		if ( isset($idUser) && is_int($idUser) )
		{
			$_SESSION['idUser'] = $idUser;
			return true;
		}
		
		return false;
	}


	/*
	 * 对客户端选择了“记住我”的cookie处理
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
	 * @return bool 
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
	 * @return bool
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
	 * @return int
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


	static public function GetCurrentUserInfo( $one_item=null )
	{
		if ( $id_user = self::GetCurrentUserId() )
		{
			$user_info = self::GetUserInfoById($id_user,$one_item);

			return $user_info;
		}

		return null;
	}


	static public function GetUserInfoById( $idUser=null, $one_item=null )
	{
		return self::GetUserInfo('idUser',$idUser, $one_item);
	}

	static public function GetUserInfoByName( $nameScreen=null, $one_item=null )
	{
		return self::GetUserInfo('nameScreen',$nameScreen, $one_item);
	}


	/*
	 * @param	string			by_what		condition key
	 * @param	string			value		condition val, could be array in the furture
	 * @param	string			one_item 	column name, if set, only return this column.

	 * @return	array/string	user info 	array(string if one_item set). 
											(or array of array if val is array in the furture).
	 */
	static function GetUserInfo( $by_what, $value=null, $one_item=null )
	{
		switch ( $by_what ){
		case 'idUser':
			$by_what = 'id';
			if ( !is_int(intval($value)) ) return null;
			break;

		case 'nameScreen':
			if ( !self::IsValidName($value) ) return null;
			$value = JWDB::escape_string($value);
			break;

		case 'email':
			if ( !self::IsValidEmail($value) ) return null;
			// email need reverse 
			$value = JWDB::escape_string(strrev($value));
			break;


		default:
			throw new JWException("Unsupport get user info by $by_what");
		}

		$sql = <<<_SQL_
SELECT	id
		, nameScreen
		, nameFull
		, pass
		, REVERSE(email) as email
		, location
		, timestamp
		, protected
		, photoInfo
		, url
		, bio
FROM	User 
WHERE	$by_what='$value' LIMIT 1
_SQL_;

		//TODO memcache here.
		$aUserInfo 			= JWDB::GetQueryResult($sql);

		if ( empty($one_item) ){
			return $aUserInfo;
		}

		if ( isset($aUserInfo) && array_key_exists($one_item,$aUserInfo) ){
			return $aUserInfo[$one_item];
		}

		return null;
	}

	/*
	 * 修改用户信息
	 * @param 
			array() 内存修改过的用户信息，不需要修改的不要加入, 
			int		idUser, null为当前用户
		@return
			bool	成功/失败
	 */
	static public function Update($modifiedUserInfo, $idUser=null)
	{
		if ( null===$idUser )
			$idUser = self::GetCurrentUserId();

		if ( empty($modifiedUserInfo) )
			return false;

		if ( array_key_exists('email', $modifiedUserInfo) )
			$modifiedUserInfo['email'] = strrev($modifiedUserInfo['email']);

		return JWDB::UpdateTableRow('User', $idUser, $modifiedUserInfo);
	}


	/*
	 * make a user invisible.
	 * @param int
	 */
	static public function Delete( $idUser )
	{
		$sql = <<<_SQL_
DELETE 	FROM USER
WHERE	id=$idUser
_SQL_;
		// TODO
		return true;
	}


	static public function Create( $userInfo )
	{
		$db = JWDB::instance()->get_db();

		// Generate md5 password
		$userInfo['pass']	= self::CreatePassword($userInfo['pass']);

		if ( $stmt = $db->prepare( "INSERT INTO User (nameScreen,pass,email,nameFull,location,protected,photoInfo)"
								. " values (?,?,?,?,?,?,?)" ) ){
			if ( $result = $stmt->bind_param("sssssss"
											, $userInfo['nameScreen']
											, $userInfo['pass']
											, strrev($userInfo['email'])
											, $userInfo['nameFull']
											, $userInfo['location']
											, $userInfo['protected']
											, $userInfo['photoInfo']
								) ){
				if ( $stmt->execute() ){
					//JWDebug::trace($stmt->affected_rows);
					//JWDebug::trace($stmt->insert_id);
					$stmt->close();
					return true;
				}else{
					JWDebug::trace($db->error);
				}
			}
		}else{
			JWDebug::trace($db->error);
		}
		return false;
	}

	static public function IsValidName( $name )
	{
		$regexp = '/^[\w\d._\-]+$/';
		if ( 1!==preg_match($regexp, $name) )
			return false;

		return true;
	}

	static public function IsValidEmail( $email, $strict=false )
	{
		$valid = false;
		
		$regexp = '/^[\w\d._\-]+@[\w\d._\-]+$/';

		if ( preg_match($regexp, $email) ){
			if ( $strict ){
				list ($user,$domain) = split ('@', $email, 2);
				if ( gethostbynamel($domain) )
					$valid = true;
			}else{
				$valid = true;
			}
		}

		return $valid;
	}


	static public function IsExistEmail ($email)
	{
		return JWDB::ExistTableRow('User',array('email'=>strrev($email)));
	}


	static public function IsExistName ($nameScreen)
	{
		return JWDB::ExistTableRow('User',array('nameScreen'=>$nameScreen));
	}


	/*
	 * @param 	enum	pictType = ['thumb48' | 'thumb24' | 'picture']
	 * @param 	int		idUser null if current user

	 * @return 	string	url of picture
	 */
	static public function GetPictureUrl($picType='thumb48', $idUser=null)
	{
		// I changed my idea - hardcode url: http://jiwai.de/zixia/picture/$picType
	}
	
	static public function SetPicture($fileName=null, $idUser=null)
	{
		if ( null===$idUser )
			$idUser = self::GetCurrentUserId();

		if ( null===$idUser )
			throw new JWException("no session found");
	
		$now = time();

		// disable
		if ( empty($fileName) )
			return JWDB::UpdateTableRow( 'User', $idUser, array ('photoInfo' => '') );

		// if enabled, we set the timestamp of new picture
		return JWDB::UpdateTableRow( 'User', $idUser, array ( 'photoInfo' => "$now|$fileName" ) );
	}

	/*
	 * @return array ( timestamp => n, filename=> x, $filetype=> )
	 */
	static public function GetPictureInfo($photoInfo)
	{
		if ( ! preg_match('/^(\d+)\|(.+)\.([^.]+)$/',$photoInfo,$matches) )
			return null;

		return array ( 	'time'		=> $matches[1]
						, 'name'	=> $matches[2]
						, 'type'	=> $matches[3]
					);
	}


	/*
	 * @return array ( pm => n, friend => x, follower=> )
	 */
	static public function GetState($idUser=null)
	{
		if ( null===$idUser )
			throw new JWException("no idUser");

		//TODO
		//$num_pm			= JWMessage::GetMessageNum($idUser);
		//$num_fav		= JWFavorite::GetFavoriteNum($idUser);
		$num_friend		= JWFriend::GetFriendNum($idUser);
		//$num_follower	= JWFollower::GetFollowerNum($idUser);
		$num_status		= JWStatus::GetStatusNum($idUser);

		return array(	'pm'			=> 0
						, 'fav'			=> 0
						, 'friend'		=> $num_friend
						, 'follower'	=> 0
						, 'status'		=> $num_status
					);
	}
	
}
?>
