<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de SNS Class
 */
class JWSns {
	/**
	 * Instance of this singleton
	 *
	 * @var JWSns
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWSns
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
	 *	将 idFriend 添加为 idUser 的好友，并负责处理相关逻辑（是否允许添加好友，发送通知邮件等）
	 */
	static public function AddFriend($idUser, $idFriend)
	{
		// TODO check idUser permission
	
		if ( ! JWFriend::Create($idUser, $idFriend) )
			throw new JWException('JWFriend::Create failed');

		$notice_settings = JWUser::GetNotification($idFriend);

		if ( 'Y'==$notice_settings['send_new_friend_email'] )
		{
			$user	= JWUser::GetUserInfoById($idUser);
			$friend = JWUser::GetUserInfoById($idFriend);

			JWMail::SendMailNoticeNewFriend($user, $friend);
		}
		
		return true;
	}
}
?>
