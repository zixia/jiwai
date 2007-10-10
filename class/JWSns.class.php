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
	 *	发送 direct message，并发送通知邮件
	 *	@param	int		$idUserSender
	 *	@param	int		$idUserReceiver
	 *	@param	string	$message
	 */
	static public function CreateMessage($idUserSender, $idUserReceiver, $message, $device='web', $time=null)
	{
		if ( ! JWMessage::Create($idUserSender, $idUserReceiver, $message, $device, $time) )
		{
			JWLog::LogFuncName("JWMessage::Create($idUserSender, $idUserReceiver, $message, $device, $time) failed");
			return false;
		}

		$notice_settings 	= JWUser::GetNotification($idUserReceiver);
		$need_notice_mail	= ('Y'==$notice_settings['send_new_direct_text_email']);

		$sender_row 	= JWUser::GetUserInfo($idUserSender);
		$receiver_row 	= JWUser::GetUserInfo($idUserReceiver);

		if ( $need_notice_mail )
			JWMail::SendMailNoticeDirectMessage($sender_row, $receiver_row, $message, JWDevice::GetNameFromType($device) );

		JWLog::Instance()->LogFuncName(LOG_INFO, "JWMessage::Create($idUserSender, $idUserReceiver, $message, $device, $time) "
											.",\tnotification email "
											. ( $need_notice_mail ? 'sent. ' : 'web')
								);
	
		
		$message = "$sender_row[nameScreen]: $message (可直接回复 'D $sender_row[nameScreen] 你想说的悄悄话')";

		JWNudge::NudgeToUsers( array($idUserReceiver), $message, 'direct_messages', $device );

		return true;
	}


	// 先提出来，以后可能又相关操作
	static public function CreateUser($userRow)
	{
		$user_id = JWUser::Create($userRow);
		JWBalloonMsg::CreateUser($user_id);
		self::CreateFollowers( $user_id, array($user_id) );

		return $user_id;
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
	
		if ( ! JWFriend::Create($userRow['id'], $friendRow['id']) ) {
			throw new JWException('JWFriend::Create failed');
		}else{
			$message = "$userRow[nameScreen] ( http://JiWai.de/".UrlEncode($userRow['nameScreen'])."/ ) 将你加为好友了。";
			JWNudge::NudgeToUsers( array($friendRow['id']), $message, 'nudge', 'web' );
		}

		$notice_settings 	= JWUser::GetNotification($friendRow['id']);
		$need_notice_mail	= ('Y'==$notice_settings['send_new_friend_email']);

		if ( $need_notice_mail )
			JWMail::SendMailNoticeNewFriend($userRow, $friendRow);

		JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFriend($userRow[id],$friendRow[id]),\tnotification email "
											. ( $need_notice_mail ? 'sent. ' : 'web')
								);
	
		/* 
	 	 *	idUser 添加 friend_id 为好友后，idUser 应该自动成为 idFriend 的 Follower。
		 *	所以，被follow的人是 friend_id
		 *	2007-05-24 暂时取消这个功能，由用户主动follow.
		 *	2007-06-07 回复这个功能，自动follow.
		*/
		if ( ! JWFollower::IsFollower($friendRow['id'], $userRow['id']) )
			self::CreateFollower($friendRow, $userRow);

		JWBalloonMsg::CreateFriend($userRow['id'],$friendRow['id']);

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
	 *	@param	array or int	$idFriends	好友 id(s)
	 *	申请将 idFriends 添加为 idUser 的好友
	 */
	static public function CreateFriendRequest($idUser, $idFriend, $note='')
	{
		$friend_request_id = JWFriendRequest::Create($idUser, $idFriend, $note);
		if( $friend_request_id ) {
			$userInfo = JWUser::GetUserInfo( $idUser );
			$message = "$userInfo[nameScreen] ( http://JiWai.de/".UrlEncode($userInfo['nameScreen'])."/ ) 想和你建立好友关系，同意的话请回复(ACCEPT $userInfo[nameScreen])。";
			JWNudge::NudgeToUsers( array($idFriend), $message, 'nudge', 'web' );
		}
		JWBalloonMsg::CreateFriendRequest($idUser,$idFriend, $friend_request_id, $note);

		return $friend_request_id;
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
		}else{
			$message = "$followerRow[nameScreen] 订阅了你的更新。";
			JWNudge::NudgeToUsers(array($user_id), $message, 'nudge', 'web' );
		}

		JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFollower($userRow[idUser],$followerRow[idUser]).");
		
		JWBalloonMsg::CreateFollower($userRow['id'],$followerRow['id']);

		return true;
	}


	/*
	 *	将 idFollower 不做为 idUser 的粉丝了，并负责处理相关逻辑（发送通知邮件等）
	 */
	static public function DestroyFollowers($idUser, $idFollowers, $biDirection=false)
	{

		if( $biDirection ) {
			$userInfo = JWUser::GetUserInfo( $idUser );
		}

		foreach ( $idFollowers as $follower_id )
		{
			if ( JWFollower::IsFollower($idUser, $follower_id) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JW::DestroyFollower JWFollower::Destroy($idUser,$follower_id).");
				if ( ! JWFollower::Destroy($idUser, $follower_id) )
				{
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFollowers JWFollower::Destroy($idUser, $follower_id) failed.");
				}else
				{
					$userFollower = JWUser::GetUserInfo( $follower_id );
					$message = "$userFollower[nameScreen] 取消订阅你的更新了。";
					//JWNudge::NudgeToUsers(array($idUser), $message, 'nudge', 'web');
				}
			}else if ( $biDirection && JWFollower::IsFollower($follower_id,$idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFollower JWFollower::Destroy($follower_id, $idUser).");
				if ( ! JWFollower::Destroy($follower_id, $idUser) )
				{
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFollowers JWFollower::Destroy($follower_id, $idUser) failed.");
				}else
				{
					$message = "$userInfo[nameScreen] 取消订阅你的更新了。";
					//JWNudge::NudgeToUsers( array($follower_id), $message, 'nudge', 'web' );
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
		$idUsers = JWFollowRecursion::GetSuperior($idUser, 5);

		foreach( $idUsers as $idUser ) {

			$user_row = JWUser::GetUserInfo($idUser);

			foreach ( $idFollowers as $follower_id )
			{
				if ( $follower_id==$idUser )
					continue;

				JWSns::CreateFollower($user_row, $follower_user_rows[$follower_id]);

				if ( $isReciprocal )
					JWSns::CreateFollower($follower_user_rows[$follower_id], $user_row);
			}

		}

		return true;
	}

    /**
     * sms Invite
     */
     static public function SmsInvite($idUserFrom, $address, $message ) {
         $idUserFrom = JWDB::CheckInt( $idUserFrom);
         if( false == JWDevice::IsValid( $address, 'sms' ) )
             return false;

         $metaInfo = array(
            'type' => 'sms',
            'address' => $address,
            'message' => $message,
            'webInvite' => true,
         );
         return JWNotifyQueue::Create($idUserFrom, null, JWNotifyQueue::T_INVITE, $metaInfo );
     }

	/*
	 *	设置邀请一个设备（email/sms/im），并发送相应通知信息
	 *	@param	string or array	$message	当为 string 的时候，不去分消息类型
	 *	当为 array 的时候，要有 im / sms / email 的 key
	 *
	 */
	static public function Invite($idUser, $address, $type, $message='', $webInvite=false)
	{
		/*
		 *	支持 string & array 的 message 参数
		 */
		if ( is_string($message) )
		{
			$im_message 	= $message;
			$sms_message 	= $message;
			$email_message 	= $message;
		}
		else
		{
			$sms_message 	= $message['im'];
			$im_message 	= $message['im'];
			$email_message 	= $message['email'];
		}

		$code_invite 	= JWDevice::GenSecret(32, JWDevice::CHAR_ALL); 
		$id_invite	= JWInvitation::Create($idUser,$address,$type,$email_message, $code_invite);

		$user_rows 	= JWUser::GetUserDbRowsByIds(array($idUser));
		$user_row	= $user_rows[$idUser];

		switch ( $type )
		{
			case 'msn':
			case 'gtalk':
			case 'jabber':
				JWRobot::SendMtRaw($address, $type, $im_message);
				// 发完消息，再发邮件 :-D
			case 'email':
				JWMail::SendMailInvitation($user_row, $address, $email_message, $code_invite);
				break;

			case 'newsmth':
			case 'skype':
			case 'qq':
				// 机器人给设备发送消息
				JWRobot::SendMtRaw($address, $type, $sms_message);
				break;
			case 'sms':
				$serverAddress = ( $webInvite == true ) ?
					JWFuncCode::GetCodeFunc($address, $idUser, JWFuncCode::PRE_REG_INVITE) : null;

				JWRobot::SendMtRaw($address, $type, $sms_message, $serverAddress);
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


		$friend_relation		= JWFriend::IsFriends				($idUser, $idFriends	,true);
		$follower_relation		= JWFollower::IsFollowers			($idUser, $idFriends	,true);

		$request_friend_ids		= JWFriendRequest::GetFriendIds		($idUser);

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
			else if ( in_array($friend_id, $request_friend_ids) )
			{
				$action_rows[$friend_id]['cancel']	= true;
			}
			else if ( $idUser!=$friend_id )
			{
 				// not friend, and not myself

				$action_rows[$friend_id]['add']		= true;
			}

			// 反向也是朋友，则可以 direct_messages / nudge
			if ( $friend_relation[$friend_id][$idUser] )
			{
				if ( 'web'!=$send_via_device_rows[$friend_id] )
					$action_rows[$friend_id]['nudge']		= true;

				$action_rows[$friend_id]['d']			= true;
			}

		}
		return $action_rows;
	}


	/*

	 *	检查 $idUser 可以对 $idFriend 做哪些 Sns 操作
	 *	$return	array	actions	array 	keys: 	add/remove(friend) follow/leave nudge/d
										values: bool
	 */
	static public function GetUserAction($idUser, $idFriend)
	{
		$arr = JWSns::GetUserActions($idUser, array($idFriend));

		if ( empty($arr) )
			return array();

		return $arr[$idFriend];
	}

	/*
	 * @return array ( pm => n, friend => x, follower=> )
	 */
	static public function GetUserState($idUser)
	{
		//TODO
		$num_pm			= JWMessage::GetMessageNum($idUser);
		$num_fav		= JWFavourite::GetFavouriteNum($idUser);
		$num_friend		= JWFriend::GetFriendNum($idUser);
		$num_follower		= JWFollower::GetFollowerNum($idUser);

		//$num_status		= JWStatus::GetStatusNum($idUser);
		$num_status		= JWDB_Cache_Status::GetStatusNum($idUser);
		$num_mms		= JWStatus::GetStatusMmsNum($idUser);

		return array(
				'pm' => $num_pm,
				'fav' => $num_fav,
				'friend' => $num_friend,
				'follower' => $num_follower,
				'status' => $num_status,
				'mms' => $num_mms,
			);
	}
	
	/*
	 * 更新用户叽歪嘻嘻，并进行广播
	 */
	static public function	UpdateStatus( $idUser, $status, $device='web', $timeCreate=null, $isSignature='N', $serverAddress=null, $options=array() )
	{
		//滤除换行 并 检查签名改变
		if( null == ( $status = self::StripStatus($idUser, $status, $device, $isSignature ) ) )
			return true;

		$timeCreate = ( $timeCreate == null ) ? time() : intval( $timeCreate );
		list( $status, $idUserReplyTo, $idStatusReplyTo ) = self::FetchStatusReplyInfo( $status, $options );

		$idConference = null;
		$conference = null;
		if( false == isset( $options['idConference'] ) || null==$options['idConference'] ){
			$conference = JWConference::FetchConference( $idUser, $idUserReplyTo , $device);
			$conference = empty( $conference ) ? null : $conference;
			$idConference = empty( $conference ) ? null : $conference['id'];
		}else{
			$idConference = $options['idConference'];
			$conference = JWConference::GetDbRowById( $idConference );
		}
		
		//滤除回复字段
		if( false == empty( $conference ) ){
			$idUserReplyTo = ( $idUserReplyTo == $conference['idUser'] ) ? null : $idUserReplyTo;
			$idStatusReplyTo = ( $idUserReplyTo == $conference['idUser'] ) ? null : $idStatusReplyTo;
			$userConference = JWUser::GetUserInfo( $conference['idUser'] );
			$status = preg_replace( '/(\s*@\s*'.$userConference['nameScreen'].'\s+)/i', '', $status );
		}

		//过滤处理
		if( false == isset( $options['filterConference'] ) ){
			$options['filterConference'] = ( $conference ) ? ( $conference['filter'] == 'Y' ) : false;
		}

		/**
		* 参数用来 JWStatus::Create 方法，新建一条更新
		*/
		$createOptions = array(
				'idUserReplyTo' => $idUserReplyTo,
				'idStatusReplyTo' => $idStatusReplyTo,
				'idConference' => $idConference,
				'timeCreate' => $timeCreate,
			);

		$acceptKeys = array( 'idPicture', 'isMms', 'idPartner' );
		foreach( $acceptKeys as $key ) {
			if( isset( $options[ $key ] ) ) {
				$createOptions[ $key ] = $options[ $key ];
			}	
		}

		/*
		 *  判断是否需要FilterConference，则叽歪更新，照常发布，但设置idConference = null；
		 *  将Status的ID记录下，以期以后补上idConference;
		 */
		if( true == $options['filterConference'] ) {
			$createOptions['idConference'] = null;
			$options['notify'] = false;
		}

		/*
		 * 决定通知方式
		 *
		 */
		if( false == isset( $options['notify'] ) ) {
			if( $conference == null )
			{
				$options['notify'] = 'ALL';
			}
			else
			{
				$options['notify'] = ( $conference['notify'] == 'Y' ) ? 'ALL' : false;
			}
		}


		//Real Create Status
		$idStatus = JWStatus::Create( $idUser, $status, $device, $timeCreate, $isSignature, $createOptions);
		if( $idStatus ) {

			$metaOptions = array(
				'idStatus' => $idStatus,
				'idConference' => $createOptions['idConference'],
				'idUserConference' => ( $createOptions['idConference'] ) ? $conference['idUser'] : null,
				'notify' => $options['notify'],
				'isMms' => isset( $createOptions['isMms'] ) ? $createOptions['isMms'] : false,
			);

			if( $options['notify'] === false ) {

				/**
				 * 通知论坛模式管理后台时，我们要告知，某条更新是属于某个会议的；
				 */

				$metaOptions['idConference'] = $idConference;
				$metaOptions['idUserConference'] = ( $conference ) ? $conference['idUser'] : null;

				$metaInfo = array(
					'idStatus' => $idStatus,
					'idUserReplyTo' => $idUserReplyTo,
					'device' => $device,
					'status' => $status,
					'options' => $metaOptions,
				);
				
				$queueType = JWQuarantineQueue::T_CONFERENCE;
				JWQuarantineQueue::Create( $idUser, $metaOptions['idUserConference'], $queueType, $metaInfo);
				return true;

			}else{
				/**
				 * 更新被正常发布了，那么我们就应该去通知用户；
				 */
				$metaInfo = array();
				$queueType = JWNotifyQueue::T_STATUS;
				$metaInfo = array(
					'message' => $status,
					'options' => $metaOptions,
				);

				if( isset($createOptions['isMms']) && $createOptions['isMms'] == 'Y' ) 
				{
					$userInfo = JWUser::GetUserInfo( $idUser );
					$mmsRow = JWPicture::GetDbRowById( $createOptions['idPicture'] );
					$picUrl = 'http://JiWai.de/' . UrlEncode($userInfo['nameScreen']) . '/mms/' . $idStatus;
					$nameScreen = $userInfo['nameScreen'];
					$message = array(
						'sms' => JWNotify::GetPrettySender($userInfo). ": 我上传了彩信<$mmsRow[fileName]>，回复 DM 免费下载。",
						'im' => JWNotify::GetPrettySender($userInfo). ": $status 彩信<$mmsRow[fileName]>地址：$picUrl",
					);

					$metaInfo['message'] = $message;

					$queueType = JWNotifyQueue::T_MMS;
				}
				JWNotifyQueue::Create( $idUser, $idUserReplyTo, $queueType, $metaInfo );
			}

			// Referesh facebook
			if ( JWFacebook::Verified( $idUser ) ) {
				JWFacebook::RefreshRef($idUser);
			}

			//Activate User
			JWUser::ActivateUser( $idUser );
		}
		return $idStatus;
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
			$ret = JWUser::SetSendViaDevice($user_id, $type);

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

		$device_row 	= JWDevice::GetDeviceDbRowById($idDevice);

		$user_id				= $device_row['idUser'];
		$destroy_device_type	= $device_row['type'];

		$send_via_device	= JWUser::GetSendViaDevice($user_id);

		if ( $destroy_device_type==$send_via_device )
				JWUser::SetSendViaDevice( $user_id, 'web');

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
     * 完成邀请用户注册 
     */
    static public function FinishInvite($idUser, $idInviter)
    {
        $idUser = JWDB::CheckInt( $idUser );
        $idInviter = JWDB::CheckInt( $idInviter );
        JWSns::CreateFriends    ( $idUser, array($idInviter), true );
        JWSns::CreateFollowers  ( $idUser, array($idInviter), true );

        return true;
    }

        /*
	 *	将 idFriend 不做为 idUser 的好友了，并负责处理相关逻辑（是否双向决裂等）
	 */
	static public function DestroyFriends($idUser, $idFriends, $biDirection=false)
	{

		$userInfo = JWUser::GetUserInfo( $idUser );

		foreach ( $idFriends as $friend_id )
		{
			if ( JWFriend::IsFriend($idUser, $friend_id) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFriend::Destroy($idUser,$friend_id).");
				if ( ! JWFriend::Destroy($idUser, $friend_id) ) {
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFriend::Destroy($idUser, $friend_id) failed.");
				}else{
					$message = "你已经不再是 $userInfo[nameScreen] 的好友了。";
					//JWNudge::NudgeToUsers( array($friend_id), $message, 'nudge', 'web' );
				}

			}

			if ( $biDirection && JWFriend::IsFriend($friend_id,$idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFriend::Destroy($friend_id, $idUser).");

				if ( ! JWFriend::Destroy($friend_id, $idUser) ) {
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFriend::Destroy($friend_id, $idUser) failed.");
				}else{
					$friendInfo = JWUser::GetUserInfo( $friend_id );
					$message = "你已经不再是 $friendInfo[nameScreen] 的好友了。";
					//JWNudge::NudgeToUsers( array($idUser), $message, 'nudge', 'web' );
				}

			}

			self::DestroyFollowers( $friend_id, array($idUser), $biDirection );
		}

		return true;
	}


	static public function ResendPassword($idUser)
	{
		$secret = JWDevice::GenSecret(32,JWDevice::CHAR_ALL);

		if ( ! JWLogin::SaveRememberMe($idUser,$secret) )
			return false;

		$user_db_row = JWUser::GetUserInfo($idUser);

		$url = JWTemplate::GetConst('UrlResetPassword');

		$url .= '/' . $secret;

		JWMail::ResendPassword($user_db_row, $url);
	}


	static public function StripStatus( $idUser, $status=null, $device='msn', $isSignature='N' ) 
	{
		$status = preg_replace('/[\n\r]/' ,' ', $status);
		if( 'Y' == $isSignature ) {
			$status = JWStatus::HtmlEntityDecode( $status );
			if( false == JWDevice::IsSignatureChanged($idUser, $device, $status)){
				return null;
			}
		}
		return $status;
	}

	static private function FetchStatusReplyInfo( $status, $options=array() )
	{
		if( isset( $options['idUserReplyTo'] ) )
		{
			$idUserReplyTo = $options['idUserReplyTo'];
			$idStatusReplyTo = $options['idStatusReplyTo'];
		}
		else
		{
			$statusPost = JWRobotLingoBase::ConvertCorner( $status );
			$reply_info = JWStatus::GetReplyInfo($statusPost);	

			$status = empty( $reply_info ) ? $status : $statusPost;
			$idUserReplyTo = empty( $reply_info ) ? null : $reply_info['user_id'];
			$idStatusReplyTo = empty( $reply_info ) ? null : $reply_info['status_id'];
		}

		return array( $status, $idUserReplyTo, $idStatusReplyTo );
	}
}
?>
