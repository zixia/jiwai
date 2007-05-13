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
	 *	@param	array or int	$idFriends	好友 id(s)
	 *	将 idFriends 添加为 idUser 的好友，并负责处理相关逻辑（是否允许添加好友，发送通知邮件等）
	 */
	static public function AddFriend($idUser, $idFriends)
	{
		if ( !is_array($idFriends) )
			$idFriends = array ($idFriends);
		
		$friend_user_rows	= JWUser::GetUserRowsByIds($idFriends);
		$user_info			= JWUser::GetUserInfo($idUser);

		foreach ( $idFriends as $friend_id )
		{
			if ( JWFriend::IsFriend($idUser, $friend_id) )
				continue;

			// TODO check idUser permission
		
			if ( ! JWFriend::Create($idUser, $friend_id) )
				throw new JWException('JWFriend::Create failed');

			$notice_settings = JWUser::GetNotification($friend_id);

			if ( 'Y'==$notice_settings['send_new_friend_email'] )
			{
				JWMail::SendMailNoticeNewFriend($user_info, $friend_user_rows[$friend_id]);

				JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($idUser,$friend_id),\tnotification email SENT.");
			}
			else
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($idUser,$friend_id),\tNO notification email.");
			}
		
			// idUser 添加 friend_id 为好友后，idUser 应该自动成为 idFriend 的 Follower。
			// 所以，被follow的人是 friend_id
			self::AddFollower($friend_id, $idUser);
		}

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


	/*
	 *	设置邀请一个设备（email/sms/im），并发送相应通知信息
	 *
	 */
	static public function Invite($idUser, $address, $type, $message)
	{
		$code_invite 	= JWDevice::GenSecret(32, JWDevice::CHAR_ALL); 
		$id_invite		= JWInvitation::Create($idUser,$address,$type,$message, $code_invite);

		$user_info = JWUser::GetUserInfo($idUser);

		if ( 'email'==$type ){
			JWMail::SendMailInvitation($user_info, $address, $message, $code_invite);
		}else{	// SMS / IM
			JWDevice::Create($user_info_invitee['id'], $address, $type);
			// TODO
			// 机器人给设备发送消息
			die ( "UNFINISHED");
		}

		return $id_invite;
	}

	/*
	 *	检查 $idUser 可以对 $idFriend 做哪些 Sns 操作
	 *	$return	array	actions	array 	keys: 	add/remove(friend) follow/leave nudge/d
										values: bool
	 */
	static public function GetUserAction($idUser, $idFriend)
	{
		if ( empty($idUser) || empty($idFriend) )
			return array();

		$action = array();

		if ( JWFriend::IsFriend($idUser, $idFriend) )
		{
			$action['remove']		= true;

			if ( JWFollower::IsFollower($idFriend, $idUser) )
				$action['leave']	= true;
			else
				$action['follow']	= true;
		}
		else if ( $idUser!=$idFriend )
		{
 			// not friend, and not myself
			$action['add']		= true;
		}

		// 反向也是朋友，则可以 direct_message / nudge
		if ( JWFriend::IsFriend($idFriend,$idUser) )
		{
			$action['nudge']		= true;
			$action['d']			= true;
		}

		return $action;
	}

	/*
	 * @return array ( pm => n, friend => x, follower=> )
	 */
	static public function GetUserState($idUser)
	{
		//TODO
		//$num_pm			= JWMessage::GetMessageNum($idUser);
		$num_fav		= JWFavourite::GetFavouriteNum($idUser);
		$num_friend		= JWFriend::GetFriendNum($idUser);
		$num_follower	= JWFollower::GetFollowerNum($idUser);
		$num_status		= JWStatus::GetStatusNum($idUser);

		return array(	'pm'			=> 0
						, 'fav'			=> $num_fav
						, 'friend'		=> $num_friend
						, 'follower'	=> $num_follower
						, 'status'		=> $num_status
					);
	}
	

}
?>
