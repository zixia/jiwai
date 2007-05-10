<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Invite Class
 */
class JWInvite {
	/**
	 * Instance of this singleton
	 *
	 * @var JWInvite
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWInvite
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
	 *	当前用户邀请邮件地址加入网站
	 *	@idUser		int		发送邀请者idUser
	 *	@address	string	被邀请者的地址
	 *	@type		string	被邀请者地址的类型(msn/sms/email... )
	 *	@message	string	用户写给被邀者的话
	 *	@return		idUser	被邀请用户的 idUser，失败为 null;
	 */
	static public function Invite($idUser, $address, $type, $message='')
	{
		die ( "过期函数" );
if ( 0 )
{
		if ( ! JWDevice::IsValid($address,$type) ){
			JWLog::Instance()->Log(LOG_NOTICE, "JWInvite::Invite($address, $type, ...) found invalid address");
			return null;
		}

		$user_info_logined	= JWUser::GetCurrentUserInfo();


		/*
		 *	为被邀请用户在用户表中建立一个未被激活的，nameScreen有32个字符的临时帐号
		 */
		$user_info_invitee	= array ( 'protected' => 'Y', 'isActive' => 'N' );


		// 生成临时 nameScreen（GenSecret CHAR_ALL 时，返回的字符串的第一个字符不会为数字）
		$user_info_invitee['nameScreen']	= JWDevice::GenSecret(32, JWDevice::CHAR_ALL); 

		// 用户密码也为邀请代码，系统会同时记录在 Invitation 表中，并且要保证唯一（todo?)
		$user_info_invitee['pass']			= JWDevice::GenSecret(32, JWDevice::CHAR_ALL);

		if ( 'email'==$type ){
			$user_info_invitee['email']		= $address;
		}


		// 创建！ 并获取 id 
		$user_info_invitee['id']	= JWUser::Create($user_info_invitee);

		if ( ! $user_info_invitee['id'] ){
			JWLog::Instance()->Log(LOG_ERR, "JWInvite::Invite($address,$type,...) JWUser::Create failed");
			return null;
		}

		// 记录在 Invitation 表中
		self::Create($idUser, $user_info_invitee['id'], $message, $user_info_invitee['pass']);

		if ( 'email'==$type ){
			JWMail::SendMailInvitation($user_info_logined, $address, $message, $user_info_invitee['pass']);
		}else{	// SMS / IM
			JWDevice::Create($user_info_invitee['id'], $address, $type);
			// TODO
			// 机器人给设备发送消息
			die ( "UNFINISHED");
		}

		return $user_info_invitee['id'];
} // if ( 0 );
	}


	/*
	 *	idUser 邀请另外一个用户
	 *
	 */
	static public function Create($idUser, $address, $type, $message, $inviteCode)
	{
		$idUser		= intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('id not int');

		if ( ! JWDevice::IsValid($address,$type) ){
			JWLog::Instance()->Log(LOG_NOTICE, "JWInvite::Create($idUser, $address, $type, ...) found invalid address");
			return null;
		}


		$sql = <<<_SQL_
INSERT INTO	Invitation
SET 		idUser			= $idUser
			, address		= '$address'
			, type			= '$type'
			, message		= '$message'
			, code			= '$inviteCode'
			, timeCreate	= NOW()
_SQL_;

		try
		{
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			JWLog::Instance()->Log(LOG_ERR, $e.toText() );
			return null;
		}

		return JWDB::GetInsertId();
	}


	/*
	 *
	 *
	 */
	static public function GetInviteInfoById($idInvitation)
	{
		$idInvitation = intval($idInvitation);
		if ( 0>=$idInvitation )
			throw new JWException ('must int');

		return JWDB::GetTableRow('Invitation', array('id'=>$idInvitation));
	}

	static public function GetInviteInfoByCode($code)
	{
		return JWDB::GetTableRow('Invitation', array('code'=>$code));
	}

	static public function GetInviterIdByCode($code)
	{
		$row = self::GetInviteInfoByCode($code);
		return $row['idUser'];
	}
}
?>
