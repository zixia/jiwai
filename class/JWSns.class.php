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
	 *	根据详细信息建立两个人的好友关系
	 *	@param	array	$userInfo
	 *	@param	array	$friendInfo
	 */
	static public function AddFriend($userInfo, $friendInfo)
	{
		if ( $friendInfo['id']==$userInfo['id'] )
		{
			JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($userInfo[id]...) is self");
			return true;
		}

		if ( JWFriend::IsFriend($userInfo['id'], $friendInfo['id']) )
		{
			JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($userInfo[id],$friendInfo[id]) already friends");
			return true;
		}

		// TODO check idUser permission
	
		if ( ! JWFriend::Create($userInfo['id'], $friendInfo['id']) )
			throw new JWException('JWFriend::Create failed');

		$notice_settings 	= JWUser::GetNotification($friendInfo['id']);
		$need_notice_mail	= ('Y'==$notice_settings['send_new_friend_email']);

		if ( $need_notice_mail )
			JWMail::SendMailNoticeNewFriend($userInfo, $friendInfo);

		JWLog::Instance()->Log(LOG_INFO, "JWSns::AddFriend($userInfo[id],$friendInfo[id]),\tnotification email "
											. ( $need_notice_mail ? 'sent. ' : '')
								);
	
		// idUser 添加 friend_id 为好友后，idUser 应该自动成为 idFriend 的 Follower。
		// 所以，被follow的人是 friend_id
		if ( ! JWFollower::IsFollower($friendInfo['id'], $userInfo['id']) )
			self::AddFollower($friendInfo['id'], $userInfo['id']);

		return true;
	}

	/*
	 *	@param	array or int	$idFriends	好友 id(s)
	 *	将 idFriends 添加为 idUser 的好友，并负责处理相关逻辑（是否允许添加好友，发送通知邮件等）
	 */
	static public function AddFriends($idUser, $idFriends, $isReciprocal=false)
	{
		if ( !is_array($idFriends) )
			throw new JWException('must array');
		
		$friend_user_rows	= JWUser::GetUserRowsByIds($idFriends);
		$user_info			= JWUser::GetUserInfo($idUser);

		$user_notice_settings 	= JWUser::GetNotification($idUser);
		$user_need_notice_mail	= ('Y'==$user_notice_settings['send_new_friend_email']);

		foreach ( $idFriends as $friend_id )
		{
			if ( $friend_id==$idUser )
				continue;

			JWSns::AddFriend($user_info, $friend_user_rows[$friend_id]);

			if ( $isReciprocal )
				JWSns::AddFriend($friend_user_rows[$friend_id], $user_info);
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

		if ( $idUser==$idFriend )
			return $action;

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
	

	static public function	UpdateStatus( $idUser, $status, $device='web' )
	{
		// FIXME: Follower 最多可以有多少位？
		$follower_ids = JWFollower::GetFollower($idUser);

		if ( !empty($follower_ids) )
		{
			$user_name_screen = JWUser::GetUserInfo($idUser, 'nameScreen');
			JWNudge::NudgeUserIds($follower_ids, "$user_name_screen: $status");
		}

		return JWStatus::Create($idUser,$status,$device);
	}


	/*
	 *	验证设备，如果通过验证，则设置最新验证设备为接收设备
	 *	@return	int		成功返回$idUser 失败返回false
	 */
	static public function VerifyDevice($address, $type, $secret)
	{
		$ret = false;

		$user_id = JWDevice::Verify($address , $type , $secret);
		
		if ( $user_id )
		{
			if ( 'sms'!=$type )
				$type = 'im';

			JWUser::SetSendViaDevice($user_id, $type);
		}

		if ( !$ret )
			return false;

		return $user_id;
	}

}
?>
