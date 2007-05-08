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
		if ( JWFriend::IsFriend($idUser, $idFriend) )
			return true;

		// TODO check idUser permission
		
		if ( ! JWFriend::Create($idUser, $idFriend) )
			throw new JWException('JWFriend::Create failed');

		$notice_settings = JWUser::GetNotification($idFriend);

		if ( 'Y'==$notice_settings['send_new_friend_email'] )
		{
			$user	= JWUser::GetUserInfoById($idUser);
			$friend = JWUser::GetUserInfoById($idFriend);

			JWMail::SendMailNoticeNewFriend($user, $friend);

			JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($idUser,$idFriend),\tnotification email SENT.");
		}
		else
		{
			JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($idUser,$idFriend),\tNO notification email.");
		}
		
		// idUser 添加 idFriend 为好友后，idUser 应该自动成为 idFriend 的 Follower。
		// 所以，被follow的人是 idFriend
		self::AddFollower($idFriend, $idUser);

		return true;
	}


	/*
	 *	将 idFollower 添加为 idUser 的粉丝，并负责处理相关逻辑（是否允许添加粉丝，发送通知邮件等）
	 */
	static public function AddFollower($idUser, $idFollower)
	{
		if ( JWFollower::IsFollower($idUser, $idFollower) )
			return true;

		// TODO check idUser permission
		
		if ( ! JWFollower::Create($idUser, $idFollower) )
			throw new JWException('JWFollower::Create failed');

		JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFollower($idUser,$idFollower),\tNO notification email.");
		
		return true;
	}

}
?>
