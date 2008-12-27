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
		$name_or_email	= JWDB::EscapeString($name_or_email);
		$pass 			= JWDB::EscapeString($pass);


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
		
		//凡是通过Web改了密码，为WebUser
		return JWDB_Cache::UpdateTableRow( 'User', $idUser, array('pass'=>$md5_pass, 'isWebUser'=>'Y', ) );
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

		$md5_pass = self::GetUserInfo($idUser,'pass');

		if ( crypt($password,$md5_pass)!=$md5_pass )
			return false;

		return true;
	}


	static public function GetCurrentUserInfo( $one_item=null )
	{
		if ( $id_user = JWLogin::GetCurrentUserId() )
		{
			$user_info = self::GetUserInfo($id_user,$one_item);

			// maybe user be deleted in database.
			if ( !empty($user_info) )
				return $user_info;
		}

		JWLogin::Logout();
		return null;
	}

	static public function GetDbRowsByIdsAndOrderByActivate($user_ids, $limit=60)
	{
		if ( empty($user_ids) )
			return array();

		if ( false==is_array($user_ids) )
			throw new JWException('must array');

		$user_ids = array_unique( $user_ids );

		$condition_in = JWDB::GetInConditionFromArray($user_ids);

		$sql = <<<_SQL_
SELECT	*, id AS idUser 
FROM	User
WHERE
	id IN ($condition_in)
ORDER BY timeStamp DESC
LIMIT $limit
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		$user_map = array();

		if ( empty($rows) )
			return array();

		foreach ( $rows as $row )
		{
			$row['email'] = strrev($row['email']);
			$user_map[$row['idUser']] = $row;
		}

		return $user_map;
	}

	/*
	 *	根据 idUser 获取 Row 的详细信息
	 *	@param	array	idUser
	 * 	@return	array	以 idUser 为 key 的 status row
	 * 
	 */
	static public function GetDbRowsByIds($user_ids, $limit=9999)
	{
		if ( empty($user_ids) )
			return array();

		if ( false==is_array($user_ids) )
			throw new JWException('must array');

		$user_ids = array_unique( $user_ids );

		$condition_in = JWDB::GetInConditionFromArray($user_ids);

		$sql = <<<_SQL_
SELECT	*, id as idUser
FROM	User
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		$user_map = array();

		if ( empty($rows) )
			return array();

		foreach ( $rows as $row )
		{
			$row['email'] = strrev($row['email']);
			$user_map[$row['idUser']] = $row;
		}

		return $user_map;
	}


	static public function GetDbRowById($idUser)
	{
		$user_db_rows = self::GetDbRowsByIds(array($idUser));

		if ( empty($user_db_rows) )
			return array();

		return $user_db_rows[$idUser];
	}

	static public function GetDbRowByIdConference( $conference_id )
	{
		$conference_id = JWDB::CheckInt( $conference_id );

		$conference = JWConference::GetDbRowById( $conference_id );

		if ( empty( $conference ) || $conference['idUser'] )
			return array();
		
		return self::GetDbRowById( $conference['idUser'] );
	}

	/*
	 *	根据用户 nameScreen/email/idUser 返回用户信息
	 * @param	string			value		condition val, could be array in the furture
	 * @param	string			one_item 	column name, if set, only return this column.

	 * @return	array/string	user info 	array(string if one_item set). 
											(or array of array if val is array in the furture).
	 */
	static public function GetUserInfo( $value=null, $one_item=null , $by_what=null, $guess_id=false, $api=false)
	{
		if ( $by_what == null )
		{
			if ( preg_match('/@/',$value) )
			{
				$by_what = 'email';
			}
			else if ( !$api && preg_match('/^\d+$/',$value) )
			{
				$by_what = 'idUser';
			}
			else
			{
				$by_what = 'nameScreen';
			}
		}

		$user_info = array();
		switch ( $by_what )
		{
			case 'idUser':
				$user_info = JWDB_Cache_User::GetDbRowById($value);
				break;

			case 'nameScreen':
				$user_info = JWDB_Cache_User::GetDbRowByNameScreen($value);
				if ( empty($user_info) && true==$guess_id && preg_match('/^\d+$/', $value) )
				{
					$user_info = JWDB_Cache_User::GetDbRowById($value);
				}
				break;

			case 'nameUrl':
				$user_info = JWDB_Cache_User::GetDbRowByNameUrl($value);
				break;

			case 'email':
				if ( false==self::IsValidEmail($value) ) 
					return $one_item==null ? array() : null;

				$user_info = JWDB_Cache_User::GetDbRowByEmail($value);
				break;

			default:
				throw new JWException("Unsupport get user info by $by_what");
		}

		if ( empty($user_info) )
			return $one_item==null ? array() : null;

		if ( null==$one_item )
		{
			return $user_info;
		}

		if ( array_key_exists($one_item, $user_info) )
		{
			return $user_info[$one_item];
		}

		return array();
	}

	static public function GetDbRowByNameScreen($name_screen)
	{
		$name_screen = JWDB::EscapeString($name_screen);
		$sql = <<<_SQL_
SELECT *, id AS idUser
FROM
	User
WHERE
	nameScreen='$name_screen'
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		if ( empty($row) )
			return array();

		$row['email'] = strrev($row['email']);
		return $row;
	}

	static public function GetDbRowByNameUrl($name_url)
	{
		$name_url = JWDB::EscapeString($name_url);
		$sql = <<<_SQL_
SELECT *, id AS idUser
FROM
	User
WHERE
	nameUrl='$name_url'
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		if ( empty($row) )
			return array();

		$row['email'] = strrev($row['email']);
		return $row;
	}

	static public function GetDbRowByEmail($email)
	{
		if ( empty($email) )
			return array();

		$remail = strrev($email);
		$remail = JWDB::EscapeString($remail);
		$sql = <<<_SQL_
SELECT *, id AS idUser
FROM
	User
WHERE
	email='$remail'
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		if ( empty($row) )
			return array();

		$row['email'] = strrev($row['email']);
		return $row;
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
		if ( array_key_exists('nameFull', $modifiedUserInfo) ) 
			$modifiedUserInfo['nameFull'] = preg_replace( '/\xE2\x80\xAE/U', '', $modifiedUserInfo['nameFull']);

		return JWDB_Cache::UpdateTableRow('User', $idUser, $modifiedUserInfo);
	}


	/*
	 * make a user invisible.
	 * @param int
	 */
	static public function Destroy( $idUser )
	{
		return JWDB::DelTableRow('User', array(	'id'=>$idUser ));
	}


	/*
	 *	创建一个帐号
	 *	@param	userInfo	k => v 的用户信息
	 *	@return	idUser on success, false on fail
	 */
	static public function Create( $userInfo )
	{
		// Generate md5 password
		$userInfo['pass']	= self::CreatePassword($userInfo['pass']);

		if ( empty($userInfo['email']) )
			$userInfo['email']=null;

		if ( empty($userInfo['isWebUser']) )
			$userInfo['isWebUser']='Y';

		if ( empty($userInfo['protected']) )
			$userInfo['protected']='N';

		if ( empty($userInfo['ip']) ) {
			$userInfo['ip'] = JWRequest::GetClientIp();
		}

		if( empty($userInfo['nameFull'] ) ) {
			$userInfo['nameFull'] = $userInfo['nameScreen'];
		}

		if( empty($userInfo['nameUrl'] ) ) {
			$userInfo['nameUrl'] = $userInfo['nameScreen'];
		}

		if( empty($userInfo['srcRegister']) ) {
			$userInfo['srcRegister'] = null;
		}

		if( empty($userInfo['location']) ) {
			$userInfo['location'] = null;
		}

		while( self::IsExistUrl( $userInfo['nameUrl'] ) ) {
			$userInfo['nameUrl'] = JWDevice::GenSecret(8);
		}

		return JWDB_Cache::SaveTableRow( 'User' ,array(
			'timeCreate' => JWDB::MysqlFuncion_Now(),
			'nameScreen' => $userInfo['nameScreen'],
			'pass' => $userInfo['pass'],
			'email' => strrev(@$userInfo['email']), // 如果是手机注册，则为空 
			'nameFull' => preg_replace('/\xE2\x80\xAE/U','',$userInfo['nameFull']),
			'nameUrl' => @$userInfo['nameUrl'],
			'location' => @$userInfo['location'],
			'protected' => $userInfo['protected'],
			'idPicture' => @$userInfo['idPicture'],
			'isWebUser' => $userInfo['isWebUser'],
			'ipRegister' => @ip2long($userInfo['ip']),
			'srcRegister' => @$userInfo['srcRegister'],
		));
	}

	/*
	 * @desc	see code for rules
	 * @param	$name	nameScreen
	 * @return	bool	valid?
	 *
	 */
	static public function IsValidName( $name )
	{
		if (strlen($name)<4||strlen($name)>20) return false; //最少 3 byte
		if (strpos($name, ' ')!==false) return false; //不能包含空格
		if (preg_match('/^\d+$/', $name)) return false; //不能全是数字，
		if (preg_match('/^[\x{0000}-\x{0FFF}]+$/u', $name)) {
			if (mb_strlen($name)<4) return false; //纯西文字符不能短于3
		} else {
			// if (!preg_match('/[\x{1000}-\x{FFFF}].*[\x{1000}-\x{FFFF}]/u', $name)) return false; //如果包含非西文字符，则其个数不能少于2
		}
		$n = $name;
		JWUnicode::unifyName($n); //检查所属Unicode区块，具体规则见JWUnicode类
		return $n==$name;
	}

	/*
	 * @desc	see code for rules
	 * @param	$name	nameFull
	 * @return	bool	valid?
	 *
	 */
	static public function IsValidFullName( $name )
	{
		if (strlen($name)<2||strlen($name)>40) return false; //最少 2 byte
		if (preg_match('/^[\d]/', $name)) return false; //不能以半角数字开头 
		$n = $name;
		JWUnicode::unifyName($n); //检查所属Unicode区块，具体规则见JWUnicode类
		return $n==$name;
	}

	static public function IsValidEmail( $email, $strict=false )
	{
		$valid = false;
		
		$regexp = '/^[\w\-\.]+@[\w\-]+(\.[\w\-]+)*(\.[a-z]{2,})$/';

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
		@return	bool	$isExist	是否存在
	 */

	static public function IsExistEmail ($email)
	{
		if ( empty($email) )
			throw new JWException('email is empty?');

		$condition = array ( 	'email'	=> strrev($email) );

		return JWDB::ExistTableRow('User',$condition);
	}


	/*
	 *	@param		string	nameScreen
	 *	@return 	bool	is exist
	 */
	static public function IsExistName ($nameScreen)
	{
		if ( false == isset(self::$msReservedNames) ) {
			self::$msReservedNames = array ( 
				'all', 'alpha', 'api', 'asset', 'beta',
				'blog', 'bug', 'faq', 'help', 'jiwai',
				'jiwaide', 'm', 'mashup', 'public_timeline', 'root',
				'sms', 'team', 'twitter', 'wo', 'www',
			       	'zixia',
			);
		}

		if ( in_array(strtolower($nameScreen) , self::$msReservedNames ) )
			return true;

		if ( preg_match( '/^J\d{6}$/i', $nameScreen ) )   //股票相关用户不允许用户直接注册
			return true;

		return JWDB::ExistTableRow('User',array('nameScreen'=>$nameScreen));
	}

	/*
	 *	@param		string	nameUrl
	 *	@return 	bool	is exist
	 */
	static public function IsExistUrl ($nameUrl)
	{
		if ( false == isset(self::$msReservedNames) ) {
			self::$msReservedNames = array ( 
				'all', 'alpha', 'api', 'asset', 'beta',
				'blog', 'bug', 'faq', 'help', 'jiwai',
				'jiwaide', 'm', 'mashup', 'public_timeline', 'root',
				'sms', 'team', 'twitter', 'wo', 'www',
			       	'zixia',
			);
		}

		if ( in_array(strtolower($nameUrl) , self::$msReservedNames ) )
			return true;

		if ( preg_match( '/^J\d+$/i', $nameUrl ) )   //股票相关用户不允许用户直接注册
			return true;

		return JWDB::ExistTableRow('User',array('nameUrl'=>$nameUrl));
	}
	
	/*
	 *	设置用户的会议模式
 	 *	@param	idUser		int
	 *	@param	idConference	int	会议的id，如果设置为null或者0，则为未启用会议模式
	 *	@return
	 */
	static public function SetConference($idUser, $idConference = null)
	{

		$idUser = intval($idUser);

		return JWDB_Cache::UpdateTableRow( 'User', $idUser, array ( 'idConference' => $idConference ) );
	}

	
	/*
	 *	设置用户的头像
 	 *	@param	idUser		int
	 *	@param	idPicture	int	图像的id，如果设置为null或者0，则删除用户头像。
	 *	@return
	 */
	static public function SetIcon($idUser, $idPicture=null)
	{

		$idUser = intval($idUser);

		// set 0 to disable
		if ( null===$idPicture )
			return JWDB_Cache::UpdateTableRow( 'User', $idUser, array ('idPicture' => null) );

		$idPicture = intval($idPicture);

		if ( 0>=$idPicture || 0>=$idPicture )
			throw new JWException('must int');

		// if enabled, we set the timestamp of new picture
		return JWDB_Cache::UpdateTableRow( 'User', $idUser, array ( 'idPicture' => $idPicture ) );
	}

	/*
	 *	获取用户的通知设置
	 *	@param	idUser				用户id
	 *	@return	notice_settings		设置的 $k => $v array，key 有：auto_nudge_me / send_new_friend_email / send_new_direct_text_email
	 */
	static public function GetNotification($idUser)
	{
		$user_info = self::GetUserInfo($idUser);

		return array ( 	 'auto_nudge_me'	=> $user_info['noticeAutoNudge']
						,'send_new_friend_email'	=> $user_info['noticeNewFriend']
						,'send_new_direct_text_email'	=> $user_info['noticeNewMessage']
						,'allow_system_mail'	=> $user_info['allowSystemMail']
						,'is_receive_offline'	=> $user_info['isReceiveOffline']
						,'allowSystemSms' => $user_info['allowSystemSms']
						,'allowReplyType' => $user_info['allowReplyType']
						,'notReceiveTime1' => $user_info['notReceiveTime1']
						,'notReceiveTime2' => $user_info['notReceiveTime2']
						,'isNotReceiveNight' => $user_info['isNotReceiveNight']
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
		$user_info	= self::GetUserInfo($idUser);

		
		$noticeSettings['auto_nudge_me']	= isset($noticeSettings['auto_nudge_me']) 	? $noticeSettings['auto_nudge_me']:'Y';
		$noticeSettings['send_new_friend_email']	= isset($noticeSettings['send_new_friend_email']) ? $noticeSettings['send_new_friend_email']:'Y';
		$noticeSettings['send_new_direct_text_email']	= isset($noticeSettings['send_new_direct_text_email']) 	? $noticeSettings['send_new_direct_text_email']:'Y';
		$noticeSettings['allow_system_mail']	= isset($noticeSettings['allow_system_mail']) 	? $noticeSettings['allow_system_mail']:'Y';
		$noticeSettings['is_receive_offline']	= isset($noticeSettings['is_receive_offline']) 	? $noticeSettings['is_receive_offline']:'N';
		$noticeSettings['allowSystemSms']	= isset($noticeSettings['allowSystemSms']) 	? $noticeSettings['allowSystemSms']:'Y';
		$noticeSettings['allowReplyType']	= isset($noticeSettings['allowReplyType']) 	? $noticeSettings['allowReplyType']:'everyone';
		$noticeSettings['isNotReceiveNight']	= isset($noticeSettings['isNotReceiveNight']) 	? $noticeSettings['isNotReceiveNight']:'N';
		$noticeSettings['notReceiveTime1']	= isset($noticeSettings['notReceiveTime1']) 	? $noticeSettings['notReceiveTime1']:'00:00:00';
		$noticeSettings['notReceiveTime2']	= isset($noticeSettings['notReceiveTime2']) 	? $noticeSettings['notReceiveTime2']:'06:00:00';

		if ( $user_info['noticeAutoNudge']!=$noticeSettings['auto_nudge_me'] )
				$db_change_set['noticeAutoNudge'] = $noticeSettings['auto_nudge_me'];


		if ( $user_info['noticeNewFriend']!=$noticeSettings['send_new_friend_email'] )
				$db_change_set['noticeNewFriend'] = $noticeSettings['send_new_friend_email'];

		if ( $user_info['noticeNewMessage']!=$noticeSettings['send_new_direct_text_email'] )
				$db_change_set['noticeNewMessage'] = $noticeSettings['send_new_direct_text_email'];

		if ( $user_info['allowSystemMail']!=$noticeSettings['allow_system_mail'] )
				$db_change_set['allowSystemMail'] = $noticeSettings['allow_system_mail'];

		if ( $user_info['isReceiveOffline']!=$noticeSettings['is_receive_offline'] )
				$db_change_set['isReceiveOffline'] = $noticeSettings['is_receive_offline'];

		if ( $user_info['allowSystemSms']!=$noticeSettings['allowSystemSms'] )
				$db_change_set['allowSystemSms'] = $noticeSettings['allowSystemSms'];

		if ( $user_info['allowReplyType']!=$noticeSettings['allowReplyType'] )
				$db_change_set['allowReplyType'] = $noticeSettings['allowReplyType'];

		if ( $user_info['isNotReceiveNight']!=$noticeSettings['isNotReceiveNight'] )
				$db_change_set['isNotReceiveNight'] = $noticeSettings['isNotReceiveNight'];

		if ( $user_info['notReceiveTime1']!=$noticeSettings['notReceiveTime1'])
				$db_change_set['notReceiveTime1'] = $noticeSettings['notReceiveTime1'];

		if ( $user_info['notReceiveTime2']!=$noticeSettings['notReceiveTime2'])
				$db_change_set['notReceiveTime2'] = $noticeSettings['notReceiveTime2'];

		if ( !count($db_change_set) )
			return true;

		$idUser	= intval($user_info['id']);

		return JWDB_Cache::UpdateTableRow('User', $idUser, $db_change_set);
	}

	/*
	 *	@param	array	$idUsers
	 *	@return array	$send_via_device_rows;
	 */
	static public function GetSendViaDeviceRowsByUserIds($idUsers)
	{
		$user_rows	= JWDB_Cache_User::GetDbRowsByIds($idUsers);

		$send_via_device_rows = array();

		foreach ( $idUsers as $user_id )
		{
			if ( isset($user_rows[$user_id]['deviceSendVia']) )
				$send_via_device_rows[$user_id] = $user_rows[$user_id]['deviceSendVia'];
			else
				$send_via_device_rows[$user_id] = 'web';
		}

		return $send_via_device_rows;
	}


	static public function GetSendViaDeviceByUserId($idUser)
	{
		$rows = self::GetSendViaDeviceRowsByUserIds(array($idUser));

		if ( empty($rows) )
			return array();

		return $rows[$idUser];
	}


	/*
	 *	过期函数
	 */
	static public function GetSendViaDevice($idUser)
	{
		$user_rows	= JWDB_Cache_User::GetDbRowsByIds(array($idUser));

		if ( isset($user_rows[$idUser]['deviceSendVia']) )
			return $user_rows[$idUser]['deviceSendVia'];
		else
			return 'web';
	}

	/*
	 *
	 *	@return	bool	
	 */
	static public function SetSendViaDevice($idUser, $device)
	{
		$idUser = JWDB::CheckInt($idUser);

		$supported_device_types = JWDevice::GetSupportedDeviceTypes();

		if ( !in_array($device, $supported_device_types) && 'web'!=$device )
		{
			JWLog::LogFuncName(LOG_CRIT, "SetSendViaDevice($idUser,$device) unsupported");
			$device = 'web';
		}

		return JWDB_Cache::UpdateTableRow('User', $idUser, array('deviceSendVia'=>$device));
	}

	static public function IsProtected($idUser)
	{
		$user_db_row = JWDB_Cache_User::GetDbRowById($idUser);
		return ('Y'==$user_db_row['protected']);
	}


	static public function IsSubSms($idUser)
	{
		// now we assume all user cis sub sms. (it's free)
		return true;

		$user_db_row = JWDB_Cache_User::GetDbRowById($idUser);
		return ('Y'==$user_db_row['isSubSms']);
	}


	static public function IsWebUser($idUser)
	{
		$user_db_row = JWDB_Cache_User::GetDbRowById($idUser);
		return ('Y'==$user_db_row['isWebUser']);
	}

	static public function SetWebUser($idUser, $isWebUser=true)
	{
		$idUser = intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('must int');

		$condition = array( 'isWebUser'	=> ($isWebUser ? 'Y' : 'N') );
		return JWDB_Cache::UpdateTableRow('User', $idUser, $condition);
	}


	static public function GetFeaturedUserIds($max=10)
	{
		$featured_user_info = self::GetUserInfo('featured');
		$status_row = JWDB_Cache_Status::GetStatusIdsFromUser($featured_user_info['id'], $max);

		if ( empty($status_row['status_ids']) )
			return;

		$status_db_row	= JWDB_Cache_Status::GetDbRowsByIds($status_row['status_ids']);

		$user_ids 			= array();

		foreach ( $status_row['status_ids'] as $status_id )
		{
			$status = $status_db_row[$status_id]['status'];
			if ( ! preg_match('/^(\S+)/', $status, $matches) )
				continue;

			$user_info = self::GetUserInfo($matches[1]);
			if ( empty($user_info) )
				continue;

			array_push($user_ids, $user_info['idUser']);
		}

		return $user_ids;
	}


	static public function GetNewestUserIds($max=5)
	{
		$max = intval($max);

		if ( 0==$max )
			$max = 5;

		$sql = <<<_SQL_
SELECT	id as idUser
FROM	User
WHERE	idPicture IS NOT NULL
		AND protected<>'Y'
		AND ((srcRegister IS NULL) or (srcRegister<>'ANONYMOUS'))
		ORDER BY timeCreate desc
LIMIT	$max
_SQL_;

		$user_ids = array();

		$rows = JWDB::GetQueryResult($sql,true);

		foreach ( $rows as $row )
		{
			array_push($user_ids,$row['idUser']);
		}

		return $user_ids;
	}

	static public function GetUserIdsByNameScreens($nameScreens=null){
		setType($nameScreens, 'array');
		$nameScreens = array_unique($nameScreens);
		$in_condition = "'" .(implode("','", $nameScreens)). "'";

		$sql = <<<_SQL_
SELECT id as idUser
FROM User 
WHERE nameScreen in ($in_condition);
_SQL_;

		$user_ids = array();

		$rows = JWDB::GetQueryResult($sql,true);

		foreach ( $rows as $row )
		{
			array_push($user_ids,$row['idUser']);
		}

		return $user_ids;
	}

	static public function GetSearchNameUserIds($key, $limit=100, $offset=0){

		$key	= JWDB::EscapeString($key);
		$sql = <<<_SQL_
SELECT id as idUser
FROM User 
WHERE nameScreen like '%$key%' OR nameScreen like '%$key%'
	OR nameFull like '%$key%' OR nameFull like '%$key%'
LIMIT	$offset, $limit
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);
		if (empty($rows))
			return array();

		$user_ids = array();
		foreach ( $rows as $row )
		{
			array_push($user_ids,$row['idUser']);
		}

		return $user_ids;
	}

	static public function GetSearchEmailUserIds($key, $limit=100, $offset=0){
		$email = strtolower($key);
		$userEmail = strrev( $email );
		$userEmail	= JWDB::EscapeString($userEmail);

		$sql = <<<_SQL_
SELECT id as idUser
FROM User
WHERE email='$userEmail'
LIMIT	$offset, $limit
_SQL_;

		$user_ids = array();
		$rows = JWDB::GetQueryResult($sql,true);
		if ( empty($rows) )
			return array();

		foreach ( $rows as $row )
		{
			array_push($user_ids,$row['idUser']);
		}
		
		//Search email from device
		$address_user_ids = self::GetSearchDeviceUserIds($email, array('msn','gtalk','newsmth'));

		$user_ids = array_merge($user_ids, $address_user_ids);
		$user_ids = array_unique($user_ids);

		return $user_ids;
	}

	static public function GetSearchDeviceUserIds($key, $devices=array('sms'), $limit=0,$offset=0){
		settype($devices, 'array');
		return JWDevice::GetDeviceInfoByAddress($key, $devices, 'idUser');
	}

	static public function IsAdmin($idUser)
	{
		$admin_user_db_row = self::GetUserInfo('adm');

		if ( 		1==$idUser	// zixia
				|| 32834==$idUser	// wqsemc
				|| 89==$idUser	// seek
				|| 863==$idUser	// lecause
				|| 135898==$idUser // WinnieLin
				|| $admin_user_db_row['idUser']==$idUser )
			return true;

		return false;
	}

	static public function GetIdEncodedFromIdUser($idUser = 0)
	{
		$idUser = JWDB::CheckInt( $idUser );
		$prefix = 'JW';
		return $prefix . base64_Encode( $idUser << 2 );
	}

	static public function GetIdUserFromIdEncoded($idEncoded )
	{
		$prefix = 'JW';
		if( 0 === strpos( $idEncoded, 'JW' ) ) {
			return @base64_Decode( substr( $idEncoded, 2 ) ) >> 2;
		}
		return null;
	}

	static public function ActivateUser($idUser) {
		$idUser = JWDB::CheckInt( $idUser );
		$updateRow = array(
			'timeStamp' => null,
		);
		return JWDB_Cache::UpdateTableRow( 'User', $idUser, $updateRow );
	}

	static public function CreateDriftBottle($ipName=null, $ipNameFull=null){
		
		if( null == $ipName )
			return null;

		if( null == $ipNameFull )
			$ipNameFull = $ipName;

		$uArray = array(
			'ip' => JWRequest::GetClientIp(),
			'nameScreen' => $ipName,
			'nameFull' => $ipNameFull,
			'nameUrl' => $ipName,
			'pass' => JWDevice::GenSecret(8),
			'isWebUser' => 'Y',
			'idPicture' => 27304,
			'srcRegister' => 'ANONYMOUS',
		);

		return self::Create( $uArray );
	}

	static public function IsAnonymous($user_id)
	{
		$user = self::GetDbRowById($user_id);
		if ( $user )
		{
			return 'ANONYMOUS' == $user['srcRegister'];
		}

		return false;
	}

	static public function InTimeChip( $user_id, $type='SLEEP', $default=false )
	{
		$user_id = JWDB::CheckInt($user_id);

		$user = self::GetUserInfo( $user_id );
		if ( empty($user) )
			return $default;

		$time_1 = time();
		$time_now = time();
		$time_2 = time();
		$activate = false;

		switch ( $type )
		{
			case 'SLEEP':
				$time_1 = null==@$user['notReceiveTime1'] 
					? strtotime('00:00:00') : strtotime($user['notReceiveTime1']);
				$time_2 = null==@$user['notReceiveTime2'] 
					? strtotime('00:00:00') : strtotime($user['notReceiveTime2']);
				$activate = $user['isNotReceiveNight'] == 'Y' ;
			break;
		}

		if ( $activate )
		{
			if ( $time_1 > $time_2 ) 
				$time_1 -= 86400;

			return $time_now > $time_1 && $time_2 > $time_now;
		}

		return $default;
	}

	static public function GetPossibleName($nameInput, $email=null, $type=null)
	{
		# get rid of openid http
		if ( preg_match('#^\w+://#',$nameInput) )
		{
			$user_name = preg_replace("#^\w+://#"	,""	,$nameInput);
			$user_name = preg_replace("#/.+#"	,""	,$user_name);
			$user_name = preg_replace("#\.#"	,"_"	,$user_name);
		}
		else
		{
			$user_name = $nameInput;
		}

		// zixia: 7/24/07 我们允许中文用户名，允许用户名数字打头[IM注册]；
		//$user_name = preg_replace("/[^\w]+/"	,""	,$user_name);
		$user_name = preg_replace("/\s+/"		,""	,$user_name);
		//$user_name = preg_replace("/^\d+/"		,""	,$user_name);
		
		if ( empty($user_name) && !empty($email) )
		{
			// 从邮件中取用户名，并进行特殊字符处理
			$user_name = $email;
			$user_name = preg_replace("/@.*/"	,""	,$user_name);
			//$user_name = preg_replace("/\./"	,""	,$user_name);

			// 如果是手机用户或者QQ用户 
			if ( preg_match('/^\d+$/',$user_name) )
			{
				$user_name = $type . $user_name;
			}
			else
			{
				$user_name = preg_replace("/^\d+/"	,""	,$user_name);
			}
		}
		

		JWUnicode::unifyName($user_name);
		/*
		 *	处理名字过短的问题
		 *	如果是3个字符的名字，那么通过
		 *	如果是1、2个字符的名字，则随机填充到4个字符
		 */
		$user_name_len = strlen($user_name);
		if ( 16<$user_name_len )
		{
			$user_name = substr($user_name, 0, 16);
		}

		if ( 3>$user_name_len )
		{
			for ( $n=$user_name_len; $n<4; $n++ )
				$user_name .= rand(0,9);
		}

		$is_valid_name = false;

		if ( ! JWUser::IsExistName($user_name) )
		{
			$is_valid_name = true;
		}
		else
		{
			$n = 1;
			while ( $n++ < 30 )
			{
				if ( ! JWUser::IsExistName("$user_name$n") )
				{
					$user_name .= $n;

					$is_valid_name = true;
					break;
				}
			}
		}

		/*
		 *	尝试了这么多个用户名都不行，加个日期尾巴看看
		 */
		if ( ! $is_valid_name )
		{
			$month_day = date("md");
			if ( ! JWUser::IsExistName("$user_name$month_day") )
			{
				$user_name 	.= $month_day;

				$is_valid_name = true;
			}
		}

		if ( !$is_valid_name )
			return null;

		return $user_name;
	}
	
	/**
	 * 获取注册来源
	 */
	static public function FetchSrcRegisterFromRobotMsg($robotMsg)
	{	
		if( empty( $robotMsg ) )
			return null;

		$body = $robotMsg->GetBody();
		$address = $robotMsg->GetAddress();
		$serverAddress = $robotMsg->GetHeader('serveraddress');
		$type = $robotMsg->GetType();

		if( $type == 'sms' ) 
		{
			$preAndId = JWFuncCode::FetchPreAndId( $serverAddress, $address );
			if( false == empty( $preAndId ) )
			{
				switch( $preAndId['pre'] )
				{
					case JWFuncCode::PRE_CONF_CUSTOM:
						$conference = JWConference::GetDbRowFromNumber( $preAndId['id'] );
						if( false == empty($conference) )
							return 'CO-' . $conference['id'];
					break;
					case JWFuncCode::PRE_CONF_IDUSER:
						if( $preAndId['id'] )
						{
							$user = JWDB_Cache_User::GetDbRowById( $preAndId['id'] );
							if( false == $user )
								return 'CO-' . $user['idConference'];
						}
					break;
					case JWFuncCode::PRE_REG_INVITE:
						if( $preAndId['id'] )
						{
							$user = JWDB_Cache_User::GetDbRowById( $preAndId['id'] );
							if( false == $user )
								return 'IN-' . $user['idConference'];
						}
					break;
				}
			}
		}

		return null;
	}
}
?>
