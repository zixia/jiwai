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
	static private $msInstance = null;


	/**
	 * Reserved Named array, init when first be used
	 *
	 * @var msReservedNames
	 */
	static private $msReservedNames = null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWUser
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


	/*
	 * 	检查用户名/email 和 密码是否匹配
	 *	@return	bool	pass/fail
	 */
	static public function GetUserFromPassword($name_or_email, $pass)
	{
		$db = JWDB::GetDb();

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

		$user_id = intval($arr['idUser']);

		#
		# Step 2. 检查密码是否匹配
		#
		if ( ! self::VerifyPassword($user_id, $pass) )
			return null;

		return $user_id;
	}


	/*
	 * @param string
	 * @param int
	 * @return bool
	 */
	static function ChangePassword($idUser, $plainPassword)
	{
		// not permit empty pass
		if ( empty($plainPassword) )
			return false;

		$idUser = intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('must int');

		$md5_pass = self::CreatePassword($plainPassword);

		return JWDB::UpdateTableRow( 'User', $idUser, array('pass'=>$md5_pass) );
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
	static public function VerifyPassword($idUser, $password)
	{
		$idUser = intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('must int');

		$md5_pass = self::GetUserInfoById($idUser,'pass');

		if ( crypt($password,$md5_pass)!=$md5_pass )
			return false;

		return true;
	}


	static public function GetCurrentUserInfo( $one_item=null )
	{
		if ( $id_user = JWLogin::GetCurrentUserId() )
		{
			$user_info = self::GetUserInfoById($id_user,$one_item);

			// maybe user be deleted in database.
			if ( !empty($user_info) )
				return $user_info;
			else
				JWLogin::Logout();
		}

		JWLogin::Logout();
		return null;
	}

	/*
	 *	根据 idUser 获取 Row 的详细信息
	 *	@param	array	idUser
	 * 	@return	array	以 idUser 为 key 的 status row
	 * 
	 */
	static public function GetUserRowById( $idUsers)
	{
		if ( !is_array($idUsers) )
			throw new JWException('must array');

		$idUsers = array_unique($idUsers);

		$reduce_function_content = <<<_FUNC_
			if ( !empty(\$v) )
				\$v .= ",";

			return \$v . intval(\$idUser);
_FUNC_;
		$condition_in = array_reduce(	$idUsers
										, create_function(
												'$v,$idUser'
												,"$reduce_function_content"
											)
										,''
									);
		$sql = <<<_SQL_
SELECT	*, id as idUser
FROM	User
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		foreach ( $rows as $row )
		{
			$row['email']				= strrev($row['email']);
			$user_map[$row['idUser']] 	= $row;
		}

		return $user_map;
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
	static private function GetUserInfo( $by_what, $value=null, $one_item=null )
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
SELECT	*
FROM	User 
WHERE	$by_what='$value' LIMIT 1
_SQL_;

		//TODO memcache here.
		$aUserInfo 			= JWDB::GetQueryResult($sql);

		$aUserInfo['email']	= strrev($aUserInfo['email']);

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
	static public function Modify($idUser, $modifiedUserInfo)
	{
		$idUser = intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('must int');

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
	static public function Destroy( $idUser )
	{
		$sql = <<<_SQL_
DELETE 	FROM USER
WHERE	id=$idUser
_SQL_;
		// TODO
		return true;
	}


	/*
	 *	创建一个帐号
	 *	@param	userInfo	k => v 的用户信息
	 *	@return	idUser on success, false on fail
	 */
	static public function Create( $userInfo )
	{
		$db = JWDB::Instance()->GetDb();

		// Generate md5 password
		$userInfo['pass']	= self::CreatePassword($userInfo['pass']);

		if ( $stmt = $db->prepare( "INSERT INTO User (timeCreate,nameScreen,pass,email,nameFull,location,protected,isActive)"
								. " values (NOW(),?,?,?,?,?,?,?)" ) ){
			if ( $result = $stmt->bind_param("sssssss"
											, $userInfo['nameScreen']
											, $userInfo['pass']
											, strrev($userInfo['email'])
											, $userInfo['nameFull']
											, $userInfo['location']
											, $userInfo['protected']
											, $userInfo['isActive']
								) )
			{
				if ( $stmt->execute() ){
					$stmt->close();
					return JWDB::GetInsertId();
				}else{
					JWLog::Instance()->Log(LOG_ERR, $db->error );
				}
			}
		}else{
			JWLog::Instance()->Log(LOG_ERR, $db->error );
		}
		return false;
	}

	/*
	 * @desc	1、英文字母打头（为了方便的区分 nameScreen 和 idUser，禁止nameScreen以数字打头)
	 *			2、允许数字、字母、"."、"_"、"-"作为帐号字符
	 *			3、在底层，不限制长度
	 * @param	$name	nameScreen
	 * @return	bool	valid?
	 *
	 */
	static public function IsValidName( $name )
	{
		$regexp = '/^[[:alpha:]][\w\d_\-]+$/';

		$ret = preg_match($regexp, $name);

		if ( 1!==$ret )
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

				$n=0;
				// we check A or MX is set
				do {
					if ( $n>0 )
						JWLog::Instance()->Log(LOG_NOTICE, "JWUser::IsValidEmail($email, $strict) dns retry $n times");

					if ( gethostbynamel($domain) || dns_get_mx($domain,$mxhosts) ){
						$valid = true;
						break;
					}
				} while ( $n++ < 3 );
			}else{
				$valid = true;
			}
		}

		return $valid;
	}

	/*
	 *	检查 email 是否已经被使用
	 *
	 *	@param	string	$email		将查找用户信息的 email 是否存在。
	 *	@param	bool	$isActive	true	只查找已经被激活的
									false	查找所有
		@return	bool	$isExist	是否存在
	 */

	static public function IsExistEmail ($email, $isActive)
	{
		if ( empty($email) )
			throw new JWException('email is empty?');

		$condition = array ( 	'email'	=> strrev($email) );

		if ( $isActive ){
			$condition['isActive'] = 'Y';
		}

		return JWDB::ExistTableRow('User',$condition);
	}


	/*
	 *	@param		string	nameScreen
	 *	@return 	bool	is exist
	 */
	static public function IsExistName ($nameScreen)
	{
		self::Instance();

		// XXX use db in the furture?
		if ( !isset(self::$msReservedNames) )
		{
			self::$msReservedNames = array (	
												'all'				=> true
												, 'api'				=> true
												, 'asset'			=> true
												, 'blog'			=> true
												, 'bug'				=> true
												, 'faq'				=> true
												, 'help'			=> true
												, 'jiwai'			=> true
												, 'jiwaide'			=> true
												, 'm'				=> true
												, 'mashup'			=> true
												, 'public_timeline'	=> true
												, 'sms'				=> true
												, 'team'			=> true
												, 'twitter'			=> true
												, 'wo'				=> true
												, 'www'				=> true
												, 'zixia'			=> true
											);
		}

		if ( isset(self::$msReservedNames[$nameScreen]) )
			return true;

		return JWDB::ExistTableRow('User',array('nameScreen'=>$nameScreen));
	}


	
	/*
	 *	设置用户的头像
 	 *	@param	idUser		int
	 *	@param	idPicture	int	图像的id，如果设置为null或者0，则删除用户头像。
	 *	@return
	 */
	static public function SetIcon($idUser, $idPicture=null)
	{
		// set 0 to disable
		if ( null===$idPicture )
			return JWDB::UpdateTableRow( 'User', $idUser, array ('idPicture' => '') );

		$idUser = intval($idUser);
		$idPicture = intval($idPicture);

		if ( 0>=$idPicture || 0>=$idPicture )
			throw new JWException('must int');

		// if enabled, we set the timestamp of new picture
		return JWDB::UpdateTableRow( 'User', $idUser, array ( 'idPicture' => $idPicture ) );
	}

	/*
	 *	获取用户的通知设置
	 *	@param	idUser				用户id
	 *	@return	notice_settings		设置的 $k => $v array，key 有：auto_nudge_me / send_new_friend_email / send_new_direct_text_email
	 */
	static public function GetNotification($idUser)
	{
		$user_info = self::GetUserInfoById($idUser);

		return array ( 	 'auto_nudge_me'	=> $user_info['noticeAutoNudge']
						,'send_new_friend_email'	=> $user_info['noticeNewFriend']
						,'send_new_direct_text_email'	=> $user_info['noticeNewMessage']
					);
	}


	/*
	 *	设置用户通知设置
	 *	@param	idUser			用户id
	 *	@param	noticeSettings	用户修改的设置 ( auto_nudge_me / send_new_friend_email / send_new_direct_text_email ), 
								如果isset,则设为 Y
	 */
	static public function SetNotification($idUser, $noticeSettings)
	{
		$db_change_set = array();
		$user_info	= self::GetUserInfoById($idUser);

		
		$noticeSettings['auto_nudge_me']				= isset($noticeSettings['auto_nudge_me']) 				? 'Y':'N';
		$noticeSettings['send_new_friend_email']		= isset($noticeSettings['send_new_friend_email']) 		? 'Y':'N';
		$noticeSettings['send_new_direct_text_email']	= isset($noticeSettings['send_new_direct_text_email']) 	? 'Y':'N';

		if ( $user_info['noticeAutoNudge']!=$noticeSettings['auto_nudge_me'] )
				$db_change_set['noticeAutoNudge'] = $noticeSettings['auto_nudge_me'];


		if ( $user_info['noticeNewFriend']!=$noticeSettings['send_new_friend_email'] )
				$db_change_set['noticeNewFriend'] = $noticeSettings['send_new_friend_email'];

		if ( $user_info['noticeNewMessage']!=$noticeSettings['send_new_direct_text_email'] )
				$db_change_set['noticeNewMessage'] = $noticeSettings['send_new_direct_text_email'];

		if ( !count($db_change_set) )
			return true;

		$idUser	= intval($user_info['id']);

		return JWDB::UpdateTableRow('User', $idUser, $db_change_set);
	}
}
?>
