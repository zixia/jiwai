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
	 *	@param	array	$userRow
	 *	@param	array	$friendRow
	 */
	static public function CreateFriend($userRow, $friendRow)
	{
		if ( $friendRow['id']==$userRow['id'] )
		{
			JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFriend($userRow[id]...) is self");
			return true;
		}

		if ( JWFriend::IsFriend($userRow['id'], $friendRow['id']) )
		{
			JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFriend($userRow[id],$friendRow[id]) already friends");
			return true;
		}

		// TODO check idUser permission
	
		if ( ! JWFriend::Create($userRow['id'], $friendRow['id']) )
			throw new JWException('JWFriend::Create failed');

		$notice_settings 	= JWUser::GetNotification($friendRow['id']);
		$need_notice_mail	= ('Y'==$notice_settings['send_new_friend_email']);

		if ( $need_notice_mail )
			JWMail::SendMailNoticeNewFriend($userRow, $friendRow);

		JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFriend($userRow[id],$friendRow[id]),\tnotification email "
											. ( $need_notice_mail ? 'sent. ' : 'none')
								);
	
		// idUser 添加 friend_id 为好友后，idUser 应该自动成为 idFriend 的 Follower。
		// 所以，被follow的人是 friend_id
		if ( ! JWFollower::IsFollower($friendRow['id'], $userRow['id']) )
			self::CreateFollower($friendRow, $userRow);

		return true;
	}

	/*
	 *	@param	array or int	$idFriends	好友 id(s)
	 *	将 idFriends 添加为 idUser 的好友，并负责处理相关逻辑（是否允许添加好友，发送通知邮件等）
	 */
	static public function CreateFriends($idUser, $idFriends, $isReciprocal=false)
	{
		if ( !is_array($idFriends) )
			throw new JWException('must array');
		
		$friend_user_rows	= JWUser::GetUserDbRowsByIds($idFriends);
		$user_info			= JWUser::GetUserInfo($idUser);

		$user_notice_settings 	= JWUser::GetNotification($idUser);
		$user_need_notice_mail	= ('Y'==$user_notice_settings['send_new_friend_email']);

		foreach ( $idFriends as $friend_id )
		{
			if ( $friend_id==$idUser )
				continue;

			JWSns::CreateFriend($user_info, $friend_user_rows[$friend_id]);

			if ( $isReciprocal )
				JWSns::CreateFriend($friend_user_rows[$friend_id], $user_info);
		}

		return true;
	}


	/*
	 *	将 idFollower 添加为 idUser 的粉丝，并负责处理相关逻辑（是否允许添加粉丝，发送通知邮件等）
	 */
	static public function CreateFollower($userRow, $followerRow)
	{
		$user_id 		= $userRow['idUser'];
		$follower_id 	= $followerRow['idUser'];

		// TODO check idUser permission
		
		if ( JWFollower::IsFollower	($user_id, $follower_id) )
			return true;

		if ( ! JWFollower::Create	($user_id, $follower_id) )
		{
			JWLog::Log(LOG_CRIT, "JWSns::CreateFollower($user_id, $follower_id) failed.");
			return false;
		}

		JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFollower($userRow[idUser],$followerRow[idUser]).");
		
		return true;
	}


	/*
	 *	将 idFollower 不做为 idUser 的粉丝了，并负责处理相关逻辑（发送通知邮件等）
	 */
	static public function DestroyFollowers($idUser, $idFollowers, $biDirection=false)
	{
		foreach ( $idFollowers as $follower_id )
		{
			if ( JWFollower::IsFollower($idUser, $follower_id) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JW::DestroyFollower JWFollower::Destroy($idUser,$follower_id).");
				if ( ! JWFollower::Destroy($idUser, $follower_id) )
				{
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFollowers JWFollower::Destroy($idUser, $follower_id) failed.");
				}
			}else if ( $biDirection && JWFollower::IsFollower($follower_id,$idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFollower JWFollower::Destroy($follower_id, $idUser).");
				if ( ! JWFollower::Destroy($follower_id, $idUser) )
				{
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFollowers JWFollower::Destroy($follower_id, $idUser) failed.");
				}
			}

		}
		
		return true;
	}



	/*
	 *	@param	array or int	$idFollowers	fans id(s)
	 *	将 idFollowers 添加为 idUser 的粉丝，并负责处理相关逻辑（是否允许添加粉丝?）
	 */
	static public function CreateFollowers($idUser, $idFollowers, $isReciprocal=false)
	{
		if ( !is_array($idFollowers) )
			throw new JWException('must array');
		
		$follower_user_rows	= JWUser::GetUserDbRowsByIds($idFollowers);
		$user_row			= JWUser::GetUserInfo($idUser);

		foreach ( $idFollowers as $follower_id )
		{
			if ( $follower_id==$idUser )
				continue;

			JWSns::CreateFollower($user_row, $follower_user_rows[$follower_id]);

			if ( $isReciprocal )
				JWSns::CreateFollower($follower_user_rows[$follower_id], $user_row);
		}

		return true;
	}


	/*
	 *	设置邀请一个设备（email/sms/im），并发送相应通知信息
	 *	@param	string or array	$message	当为 string 的时候，不去分消息类型
											当为 array 的时候，要有 im / sms / email 的 key
	 *
	 */
	static public function Invite($idUser, $address, $type, $message='')
	{
		$code_invite 	= JWDevice::GenSecret(32, JWDevice::CHAR_ALL); 
		$id_invite		= JWInvitation::Create($idUser,$address,$type,$message, $code_invite);

		$user_rows 	= JWUser::GetUserDbRowsByIds(array($idUser));
		$user_row	= $user_rows[$idUser];

		$im_message 	= '';
		$sms_message 	= '';
		$email_message 	= '';


		/*
		 *	支持 string & array 的 message 参数
		 */
		if ( is_string($message) )
		{
			$im_message = $sms_message = $email_message = $message;
		}
		else
		{
			$im_message = $sms_message = $message['im'];
			$email_message = $message['email'];
		}


		switch ( $type )
		{
			case 'msn':
				JWRobot::SendMtRaw($address, $type, $im_message);
				// 发完消息，再发邮件 :-D
			case 'email':
				JWMail::SendMailInvitation($user_row, $address, $email_message, $code_invite);
				break;

			case 'sms':
				// 机器人给设备发送消息
				JWRobot::SendMtRaw($address, $type, $sms_message);
				break;

			default:
				JWLog::Log(LOG_CRIT, "JWSns::Invite($idUser, $address, $type,...) not support now");
				throw new JWException("unsupport type $type");
		}

		return $id_invite;
	}


	/*
	 *	检查 $idUser 可以对 $idFriends 做哪些 Sns 操作
	 *	$return	array of array		action_rows
										array 	
											key1: 		idFriend
											value1:		array
												key2(s): 	add/remove(friend) follow/leave nudge/d
												value2: bool
									ie. array(1=>array('add'=>true,'remove'=>...), 2=>array(...))
	 */
	static public function GetUserActions($idUser, $idFriends)
	{
		if ( empty($idUser) || empty($idFriends) )
			return array();

		if ( !is_array($idFriends) )
			throw new JWException('must array');


		$friend_relation	= JWFriend::IsFriends		($idUser, $idFriends	,true);
		$follower_relation	= JWFollower::IsFollowers	($idUser, $idFriends	,true);

		$send_via_device_rows	= JWUser::GetSendViaDeviceRowsByUserIds($idFriends);

		$action_rows = array();
		foreach ( $idFriends as $friend_id )
		{
			if ( $friend_relation[$idUser][$friend_id] )
			{
				$action_rows[$friend_id]['remove']	= true;

				if ( $follower_relation[$friend_id][$idUser] )	
					$action_rows[$friend_id]['leave']	= true;
				else
					$action_rows[$friend_id]['follow']	= true;
			}
			else if ( $idUser!=$friend_id )
			{
 				// not friend, and not myself
				$action_rows[$friend_id]['add']		= true;
			}

			// 反向也是朋友，则可以 direct_message / nudge
			if ( $friend_relation[$friend_id][$idUser] )
			{
				if ( 'none'!=$send_via_device_rows[$friend_id] )
					$action_rows[$friend_id]['nudge']		= true;

				// TODO $action_rows[$friend_id]['d']			= true;
			}

		}
		return $action_rows;
	}


	/*
	 	@过期函数	替代为 GetUserActions

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
	

	/*
	 *
	 */
	static public function	UpdateStatus( $idUser, $status, $device='web', $time=null )
	{
		// FIXME: Follower 最多可以有多少位？
		$follower_ids = JWFollower::GetFollowerIds($idUser);

		if ( !empty($follower_ids) )
		{
			$user_name_screen = JWUser::GetUserInfo($idUser, 'nameScreen');
			JWNudge::NudgeUserIds($follower_ids, "$user_name_screen: $status");
		}

		return JWStatus::Create($idUser,$status,$device,$time);
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

			$ret = JWUser::SetSendViaDevice($user_id, $type);
		}

		if ( !$ret )
			return false;

		return $user_id;
	}

	/*
	 *	建立设备，则设置最新验证设备为接收设备
	 *	@return	int		成功返回$device_id 失败返回false
	 */
	static public function CreateDevice($idUser, $address, $type, $isVerified=false)
	{
		$ret = false;

		$device_id = JWDevice::Create($idUser, $address , $type , $isVerified);
		
		if ( empty($device_id) )
		{
			JWLog::LogFuncName(LOG_CRIT, "JWDevice::Create($idUser, $address , $type , $isVerified) failed.");
			return false;
		}

		if ( 'sms'!=$type )
			$type = 'im';

		if ( ! JWUser::SetSendViaDevice($idUser, $type) )
			JWLog::LogFuncName(LOG_CRIT, "JWUser::SetSendViaDevice($idUser, $type) failed."); 

		return $device_id;
	}


	/*
	 *	删除设备，同时设置用户的设置为其他设备（如果用户有绑定其他设备的话）
	 *	@return	bool
	 */
	static public function DestroyDevice($idDevice)
	{
		$ret = false;

		$device_rows 	= JWDevice::GetDeviceDbRowsByIds(array($idDevice));
		$device_row		= $device_rows[$idDevice];

		$user_id				= $device_row['idUser'];
		$destroy_device_type	= $device_row['type'];

		$send_via_device	= JWUser::GetSendViaDevice($user_id);

		if ( 'none'!=$send_via_device )
		{
			$device_map		= JWDevice::GetDeviceRowsByUserIds(array($user_id));
			$device_info	= $device_map[$user_id];

			$device_types		= array_keys($device_info);
			$left_device_types	= array_diff($device_types, array($destroy_device_type) );

			if ( !empty($left_device_types) ){
				JWUser::SetSendViaDevice( $user_id, array_shift($left_device_types) );
			} else {
				JWUser::SetSendViaDevice( $user_id, 'none');
			}
		}

		return JWDevice::Destroy($idDevice);
	}
	
	
	static public function AcceptInvitation($idInvitation)
	{
		JWInvitation::LogAccept($idInvitation);
	}


	/*
	 *	用户收到邀请后，根据邀请注册用户成功，将调用这个函数进行相关邀请关系的设置
	 *	@param	int	$idUser			接受邀请的用户注册后的 idUser
	 *	@param	int	$idInvitation	邀请 id
	 */
//FIXME	名字不对
	static public function FinishInvitation($idUser, $idInvitation)
	{
		JWInvitation::LogRegister($idInvitation, $idUser);


		$invitation_rows		= JWInvitation::GetInvitationDbRowsByIds(array($idInvitation));
		$inviter_id				= $invitation_rows[$idInvitation]['idUser'];

		$reciprocal_user_ids	= JWInvitation::GetReciprocalUserIds($idInvitation);
		array_push( $reciprocal_user_ids, $inviter_id );

		// 互相加为好友
		JWSns::CreateFriends	( $idUser, $reciprocal_user_ids, true );
		JWSns::CreateFollowers	( $idUser, $reciprocal_user_ids, true );

		return true;
	}

	/*
	 *	将 idFriend 不做为 idUser 的好友了，并负责处理相关逻辑（是否双向决裂等）
	 */
	static public function DestroyFriends($idUser, $idFriends, $biDirection=false)
	{
		foreach ( $idFriends as $friend_id )
		{
			if ( JWFriend::IsFriend($idUser, $friend_id) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFriend::Destroy($idUser,$friend_id).");

				if ( ! JWFriend::Destroy($idUser, $friend_id) )
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFriend::Destroy($idUser, $friend_id) failed.");

			}

			if ( $biDirection && JWFriend::IsFriend($friend_id,$idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFriend::Destroy($friend_id, $idUser).");

				if ( ! JWFriend::Destroy($friend_id, $idUser) )
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFriend::Destroy($friend_id, $idUser) failed.");

			}

			/*
			 *	处理 Follower
			 */
			if ( JWFollower::IsFollower($friend_id,$idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFollower::Destroy($friend_id,$idUser).");

				if ( ! JWFollower::Destroy($friend_id,$idUser) )
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFollower::Destroy($friend_id,$idUser) failed.");

			}

			if ( $biDirection && JWFollower::IsFollower($idUser,$friend_id) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFollower::Destroy($idUser,$friend_id).");

				if ( ! JWFollower::Destroy($idUser, $friend_id) )
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFollower::Destroy($idUser,$friend_id) failed.");

			}
		}

		return true;
	}

}
?>
