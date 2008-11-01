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
	static public function CreateMessage($sender_id, $receiver_id, $message, $device='web', $options=array())
	{
		if (false==isset($options['noreply_tips']))
			$options['noreply_tips'] = false;
		
		if ( JWBlock::IsBlocked( $receiver_id, $sender_id, false ) )  // receiver_id blocked sender_id;
			return false;		

		if ( false == ($message_id = JWMessage::Create($sender_id, $receiver_id, $message, $device, $options)) )
		{
			JWLog::LogFuncName("JWMessage::Create($sender_id, $receiver_id, $message, $device) failed");
			return false;
		}

		$notice_settings = JWUser::GetNotification($receiver_id);
		$need_notice_mail = ('Y'==$notice_settings['send_new_direct_text_email']);

		$sender_row = JWUser::GetUserInfo($sender_id);
		$receiver_row = JWUser::GetUserInfo($receiver_id);

		if ( $need_notice_mail )
		{
			JWMail::SendMailNoticeDirectMessage($sender_row, $receiver_row, $message, JWDevice::GetNameFromType($device), $message_id );
		}

		JWLog::Instance()->LogFuncName(LOG_INFO, 
			"JWMessage::Create($sender_id, $receiver_id, $message, $device) "
			.",\tnotification email "
			. ( $need_notice_mail ? 'sent. ' : 'web')
		);
	
		if ( false==$options['noreply_tips'] )
		{
			$message = "$sender_row[nameScreen]: $message "
					."(可直接回复 D $sender_row[nameScreen] 你想说的悄悄话)";
		}
		$message_info = array(
			'message' => $message,
			'idMessage' => $message_id,
		);

		JWNudge::NudgeToUsers( array($receiver_id), $message_info, 'direct_messages', $device );

		return $message_id;
	}


	// 先提出来，以后可能又相关操作
	static public function CreateUser($userRow)
	{
		$user_id = JWUser::Create($userRow);
		if ( $user_id ) 
		{
			JWBalloonMsg::CreateUser($user_id);
			if ( $userRow['email'] ) {
				$invitations = JWInvitation::GetInvitationIdsFromAddresses( array(array('address'=>$userRow['email'], 'type'=>'email')) );
				JWSns::NoticeInvite($user_id, $invitations);
			}
		}
		return $user_id;
	}

	static public function NoticeInvite($new_user_id, $invitations) {
		if ( is_numeric($new_user_id) ) 
		{
			$friend = JWUser::GetDbRowById($new_user_id);
		} 
		else 
		{
			$friend = $new_user_id;
		}
		if ( empty($invitations) ) return;

		$user_ids = array();
		foreach( $invitations AS $one ) {
			$user_ids[$one['idUser']] = $one['idUser'];
		}
		$user_ids = array_keys($user_ids);
		$users = JWDB_Cache_User::GetDbRowsByIds($user_ids);
		foreach( $users AS $user ) {
			JWMail::SendMailNoticeEverInvite($user, $friend);
		}
	}

	/*
	 *	@param	int $idFollower)
	 *	申请将 idFollower 互相关注 idUser
	 */
	static public function CreateFollowerRequest($idUser, $idFollower, $note='')
	{
		$idUser = JWDB::CheckInt( $idUser );
		$idFollower = JWDB::CheckInt( $idFollower );

		$follower_request_id = JWFollowerRequest::Create($idUser, $idFollower, $note);

		if( $follower_request_id ) {
			$userInfo = JWUser::GetUserInfo( $idFollower );
			$message = JWRobotLingoReply::GetReplyString( null, 'OUT_FOLLOWREQUEST_MESSAGE', array(
				$userInfo['nameScreen'],
				UrlEncode($userInfo['nameUrl']),
			));
			JWNudge::NudgeToUsers( $idUser, $message, 'nudge', 'web' );
		}
		JWBalloonMsg::CreateFollowerRequest($idUser, $idFollower, $note);

		return $follower_request_id;
	}



	/*
	 *	将 idFollower 添加为 idUser 的粉丝，并负责处理相关逻辑（是否允许添加粉丝，发送通知邮件等）
	 */
	static public function CreateFollower($idUser, $idFollower, $notification='N')
	{
		if ( JWFollower::IsFollower($idUser, $idFollower) )
		{
			return JWFollower::SetNotification($idUser, $idFollower, $notification);
		}

 		self::UnBlock( $idFollower, $idUser );

		if ( false == JWFollower::Create($idUser, $idFollower, $notification) )
		{
			JWLog::Log(LOG_CRIT, "JWSns::CreateFollower($user_id, $follower_id) failed.");
			return false;
		}
		/* for auto_follower */
		$is_reciprocal_intercept = JWThirdIntercept::IsAutoFriendShip( $idUser );
		if ( $is_reciprocal_intercept && false==JWFollower::IsFollower($idFollower, $idUser) )
		{
			JWFollower::Create($idFollower, $idUser, $notification);
		}

		JWLog::Instance()->Log(LOG_INFO, "JWSns::CreateFollower($idUser, $idFollower).");
		
		JWBalloonMsg::CreateFollower( $idUser, $idFollower );

		return true;
	}


	/*
	 *	将 idFollower 不做为 idUser 的粉丝了，并负责处理相关逻辑（发送通知邮件等）
	 */
	static public function DestroyFollowers($idUser, $idFollowers, $biDirection=false)
	{
		$idUser = JWDB::CheckInt( $idUser );

		if( $biDirection ) {
			$userInfo = JWUser::GetUserInfo( $idUser );
		}

		foreach ( $idFollowers as $follower_id )
		{
			$userFollower = JWUser::GetUserInfo( $follower_id );
			if( empty( $userFollower ) )
				continue;

			$nowBioDirection = ( $userFollower['protected'] == 'Y' );

			//destroy one;
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
			}
			
			//destroy another
			if ( ( $biDirection || $nowBioDirection ) && JWFollower::IsFollower($follower_id,$idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFollower JWFollower::Destroy($follower_id, $idUser).");
				if ( ! JWFollower::Destroy($follower_id, $idUser) )
				{
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFollowers JWFollower::Destroy($follower_id, $idUser) failed.");
				}else
				{
					$userFollower = JWUser::GetUserInfo( $follower_id );
					$message = "$userFollower[nameScreen] 取消订阅你的更新了。";
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
		
		$idUsers = JWFollowRecursion::GetSuperior($idUser, 5);

		foreach( $idUsers as $idUser ) 
		{
			$user_row = JWUser::GetUserInfo($idUser);
			foreach ( $idFollowers as $idFollower )
			{
				JWSns::CreateFollower( $idUser, $idFollower );
				$isReciprocalIntercept = JWThirdIntercept::IsAutoFriendShip( $idUser );
				if ( $isReciprocal || $isReciprocalIntercept )
					JWSns::CreateFollower( $idFollower, $idUser );
			}
		}

		return true;
	}

	/**
	 * Conference/User Sms Invite
	 */
	static public function SmsInvite($user_id, $address, $message) 
	{
		$user_id = JWDB::CheckInt( $user_id );

		JWPubSub::Instance('spread://localhost/')->Publish('/invite/sms', array(
			'type' => 'sms',
			'user_id' => $user_id,
			'address' => $address,
			'message' => $message,
		));
		
		return true;
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
			$im_message 	= $message['sms'];
			$email_message 	= $message['email'];
		}

		$code_invite 	= JWDevice::GenSecret(32, JWDevice::CHAR_ALL); 
		$id_invite	= JWInvitation::Create($idUser,$address,$type,$email_message, $code_invite);

		$user_rows 	= JWDB_Cache_User::GetDbRowsByIds(array($idUser));
		$user_row	= $user_rows[$idUser];

		if ( in_array($type, JWDevice::$emailArray) )
		{
			JWMail::SendMailInvitation($user_row, $address, $email_message, array());
		}

		if ( in_array($type, JWDevice::$smsArray ) )
		{
			JWSns::SmsInvite( $idUser, $address, $sms_message );
			return $id_invite;
		}

		if ( in_array($type, JWDevice::$imArray ) )
		{
			JWRobot::SendMtRawQueue( $address, $type, $im_message, null );
			return $id_invite;
		}

		JWLog::Log(LOG_CRIT, "JWSns::Invite($idUser, $address, $type,...) not support now");
		throw new JWException("unsupport type $type");

		return false;
	}
	
	static public function GetTagAction($user_id, $tag_id)
	{
		$init_action = array(
			'on' => false,
			'off' => false,
			'follow' => false,
			'leave' => false,
		);

		if ( empty($user_id) || empty($tag_id) )
			return $init_action;

		$action_rows = self::GetTagActions($user_id, array($tag_id) );

		return $action_rows[ $tag_id ];
	}

	static public function GetTagActions($user_id, $tag_ids)
	{
		if ( empty($user_id) || empty($tag_ids) )
			return array();

		if ( false == is_array($tag_ids) )
			throw new JWException('must array');

		$following_infos = JWTagFollower::GetFollowingInfos($user_id);

		$action_rows = array();
		$init_action = array(
			'on' => false,
			'off' => false,
			'follow' => true,
			'leave' => false,
		);

		foreach( $tag_ids as $tag_id )
		{
			$action = $init_action;
			if( isset( $following_infos[ $tag_id ] ) )
			{
				$following_info = $following_infos[ $tag_id ];
				$action['follow'] = false;
				$action['leave'] = true;
				$action['on'] = $following_info['notification'] == 'N';
				$action['off'] = $following_info['notification'] == 'Y';
			}

			$action_rows[$tag_id] = $action;
		}

		return $action_rows;
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
	static public function GetUserActions($idUser, $idOthers)
	{
		if ( empty($idUser) || empty($idOthers) )
			return array();

		if ( false == is_array($idOthers) )
			throw new JWException('must array');

		$followingInfos = JWFollower::GetFollowingInfos($idUser);

		$request_follower_ids = JWFollowerRequest::GetInRequestIds($idUser);

		$send_via_device_rows = JWUser::GetSendViaDeviceRowsByUserIds($idOthers);

		$action_rows = array();
		$init_action = array(
			'nudge' => false,
			'on' => false,
			'off' => false,
			'follow' => false,
			'leave' => false,
			'd' => false,	
		);

		foreach ( $idOthers as $other_id )
		{
			$action = $init_action;

			if( isset( $followingInfos[$other_id] ) ) {
				if( $followingInfos[$other_id]['notification'] == 'Y' ) {
					$action['off'] = true;
				}else{
					$action['on'] = true;
				}
				$action['leave'] = true;
			}else{
				$action['follow'] = true;
			}

			if( false == JWBlock::IsBlocked( $other_id, $idUser, false ) ) {
				$action['d'] = true;

				if( isset( $send_via_device_rows[$other_id] ) ) {
					if ( 'web' != $send_via_device_rows[$other_id] && $other_id != $idUser )
						$action['nudge'] = true;
				}
			}else{
				$action = $init_action;
				if( false == JWBlock::IsBlocked( $other_id, $idUser ) ) {
					$action['follow'] = true;
				}
			}
		
			$action_rows[ $other_id ] = $action;
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
		$num_following		= JWDB_Cache_Follower::GetFollowingNum($idUser);
		$num_follower		= JWDB_Cache_Follower::GetFollowerNum($idUser);

		$num_status		= JWDB_Cache_Status::GetStatusNum($idUser);
		$num_mms		= JWStatus::GetStatusMmsNum($idUser);

		return array(
				'pm' => $num_pm,
				'fav' => $num_fav,
				'following' => $num_following,
				'follower' => $num_follower,
				'status' => $num_status,
				'mms' => $num_mms,
			);
	}
	
	/*
	 * 更新用户叽歪嘻嘻，并进行广播
	 */
	static public function	UpdateStatus( $idUser, $status, $device='web', $timeCreate=null, $serverAddress=null, $options=array() )
	{
		/** deal filter **/
		$timeCreate = ( $timeCreate == null ) ? time() : intval( $timeCreate );
		if ( false==isset($options['filter']) || $options['filter'] == true )
		{
			$options['filter'] = false;
			$quarantine_array = array($idUser, $status, $device, $timeCreate, $serverAddress, $options);
			if (self::FilterStatus( $status, $quarantine_array ) )
			{
				return -1;
			}
		}
		/** end filter **/

		//滤除换行 并 检查签名改变
		$statusType = isset($options['statusType']) ? $options['statusType'] : 'NONE';
		if( null == ( $status=self::StripStatusAndCheckSignature($idUser,$status,$device,$statusType) ) )
			return true;

		/**
		 * nano format parse
		 */
		//parse vote_item
		if ( 'NONE'==$statusType && 'web'==strtolower($device) && self::ParseVoteItem($status) )
			$statusType = 'VOTE';

		//parse reply
		$reply_info = JWStatus::GetReplyInfo( $status, $options );
		
		$status = $reply_info['status'];
		$idThread = $reply_info['thread_id'];
		$idTag = $reply_info['tag_id'];
		$idUserReplyTo = $reply_info['user_id'];
		$idStatusReplyTo = $reply_info['status_id'];
		$idConference = $reply_info['conference_id'];
		$idGeocode = $reply_info['geocode_id'];

		//Conference Protected And 过滤处理
		$idConference = JWConference::IsAllowJoin( $idConference, $idUser, $device ) ? $idConference : null;
		$conference = ( $idConference ) ? JWConference::GetDbRowById( $idConference ) : null;
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
			'idThread' => $idThread,
			'idTag' => $idTag,
			'idGeocode' => $idGeocode,
			'statusType' => $statusType,
		);

		$acceptKeys = array( 'idPicture', 'statusType', 'idPartner' );
		foreach( $acceptKeys as $key ) {
			if( isset( $options[ $key ] ) && false==isset($createOptions[$key]) ) {
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
		$idStatus = JWStatus::Create( $idUser, $status, $device, $timeCreate, $createOptions);
		if( $idStatus ) {

			$status = JWStatus::SimpleFormat( $status, $idUserReplyTo );	

			$metaOptions = array(
				'idStatus' => $idStatus,
				'idConference' => $createOptions['idConference'],
				'idThread' => $idThread,
				'idUserConference' => ( $createOptions['idConference'] ) ? $conference['idUser'] : null,
				'notify' => $options['notify'],
				'statusType' => $statusType,
			);

			if( $options['notify'] === false ) 
			{

				/**
				 * 通知论坛模式管理后台时，我们要告知，某条更新是属于某个会议的；
				 */

				$metaOptions['idConference'] = $idConference;
				$metaOptions['idUserConference'] = ( $conference ) ? $conference['idUser'] : null;

				$metaInfo = array(
					'idStatus' => $idStatus,
					'idUserReplyTo' => $idUserReplyTo,
					'idThread' => $idThread,
					'device' => $device,
					'status' => $status,
					'options' => $metaOptions,
				);
				
				$queueType = JWQuarantineQueue::T_CONFERENCE;
				JWQuarantineQueue::Create( $idUser, $metaOptions['idUserConference'], $queueType, $metaInfo);
				return true;

			}
			else
			{
				/**
				 * 更新被正常发布了，那么我们就应该去通知用户；
				 */
				$metaInfo = array(
					'idStatus' => $idStatus,
					'options' => $metaOptions,
					'type' => JWNotifyQueue::T_STATUS,
				);

				JWPubSub::Instance('spread://localhost/')->Publish('/statuses/update', array(
					'idUser' => $idUser,
					'idUserReplyTo' => $idUserReplyTo,
					'metaInfo' => $metaInfo,
				));
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
	static public function CreateDevice($idUser, $address, $type, $isVerified=false, $options=array() )
	{
		$ret = false;

		$device_id = JWDevice::Create($idUser, $address , $type , $isVerified, $options);
		
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
	static public function FinishInvitation($idUser, $idInvitation)
	{
		JWInvitation::LogRegister($idInvitation, $idUser);


		$invitation_rows		= JWInvitation::GetInvitationDbRowsByIds(array($idInvitation));
		$inviter_id				= $invitation_rows[$idInvitation]['idUser'];

		$reciprocal_user_ids	= JWInvitation::GetReciprocalUserIds($idInvitation);
		array_push( $reciprocal_user_ids, $inviter_id );

		// 互相加为好友
		JWSns::CreateFollowers	( $idUser, $reciprocal_user_ids, true );

		return true;
	}

	/*
	 * 完成邀请用户注册 
	 */
	static public function FinishInvite($user_id, $invite_user_id)
	{
		$user_id = JWDB::CheckInt( $user_id );
		$invite_user_id = JWDB::CheckInt( $invite_user_id );
		JWSns::CreateFollowers  ( $invite_user_id, array($user_id), true );

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
			if ( JWFollower::IsFollower($friend_id, $idUser) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFollower::Destroy($idUser,$friend_id).");
				if ( ! JWFollower::Destroy($idUser, $friend_id) ) {
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFollower::Destroy($idUser, $friend_id) failed.");
				}else{
					$message = " $userInfo[nameScreen] 不再关注你了。";
					//JWNudge::NudgeToUsers( array($friend_id), $message, 'nudge', 'web' );
				}

			}

			if ( $biDirection && JWFollower::IsFollower($idUser, $friend_id) )
			{
				JWLog::Instance()->Log(LOG_INFO, "JWSns::DestroyFriends JWFollower::Destroy($friend_id, $idUser).");

				if ( ! JWFollower::Destroy($friend_id, $idUser) ) {
					JWLog::Log(LOG_CRIT, "JWSns::DestroyFriends JWFollower::Destroy($friend_id, $idUser) failed.");
				}else{
					$friendInfo = JWUser::GetUserInfo( $friend_id );
					$message = " $friendInfo[nameScreen] 不再关注你了。";
					//JWNudge::NudgeToUsers( array($idUser), $message, 'nudge', 'web' );
				}

			}

			self::DestroyFollowers( $friend_id, array($idUser), $biDirection );
		}

		return true;
	}

	/**
	 * idUser Block $idUserBlock
	 */
	static public function Block($idUser, $idUserBlock) {
		$idUser = JWDB::CheckInt( $idUser );
		$idUserBlock = JWDB::CheckInt( $idUserBlock );
		
		$flag = JWBlock::Create( $idUser, $idUserBlock );
		if( $flag ) {
			JWFollower::Destroy( $idUser, $idUserBlock );
			JWFollower::Destroy( $idUserBlock, $idUser );
		}
	}

	/**
	 * idUser unBlock $idUserBlock
	 */
	static public function UnBlock($idUser, $idUserBlock) {
		$idUser = JWDB::CheckInt( $idUser );
		$idUserBlock = JWDB::CheckInt( $idUserBlock );
		JWBlock::Destroy( $idUser, $idUserBlock );
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

	static public function GetIdUserVistors( $idUser, $idUserVistor = 0 , $maxNum=12 ) 
	{
		$idUser = JWDB::CheckInt( $idUser );

		$vKey = JWDB_Cache::GetCacheKeyByFunction( array( 'JWSns', 'GetIdUserVistors'), array($idUser) );
		$memcache = JWMemcache::Instance();
		
		$idVistors = $memcache->Get( $vKey );
		if( false == $idVistors ) {
			if ( $idUserVistor > 0 && $idUser != $idUserVistor && false==JWUser::IsAnonymous($idUserVistor) )
			{
				$idVistors = array( $idUserVistor );
				$memcache->Set( $vKey, $idVistors );
				return $idVistors ;
			}
			return array();
		}
		
		if( $idUserVistor > 0 && $idUser != $idUserVistor && false==JWUser::IsAnonymous($idUserVistor)) {
			$idVistors = array_unique( array_merge( array($idUserVistor), $idVistors ) );
			if( count( $idVistors ) > $maxNum ) {
				$idVistors = array_slice( $idVistors, 0, $maxNum );
			}
			$memcache->Set( $vKey, $idVistors );
		}

		return $idVistors;
	}

	static public function StripStatusAndCheckSignature( $idUser, &$status=null, $device='msn', $statusType='NONE' ) 
	{
		$status = JWTextFormat::PreFormatWebMsg( $status, $device );
		if( 'SIG' == $statusType ) 
		{
			if( false == JWDevice::IsSignatureChanged($idUser, $device, $status))
			{
				return null;
			}
		}
		return $status;
	}

	static public function ExecWeb($idUser, $status, $operateName='操作')
	{
		$robotMsg = new JWRobotMsg();
		$robotMsg->Set( $idUser , 'web', $status, 'web@jiwai.de' );
		$replyMsg = JWRobotLogic::ProcessMo( $robotMsg );

		if( $replyMsg === false ) {
			JWSession::SetInfo('error', '哎呀！由于系统故障，'.$operateName.'失败了');
			JWLog::Instance()->Log(LOG_ERR, "$status failed");
		}
		if( false == empty( $replyMsg ) ){
			JWSession::SetInfo('notice', $replyMsg->GetBody() );
		}	
	}

	static public function IsProtectedStatus( $status_row, $action_user_id )
	{
		if( JWUser::IsAdmin($action_user_id ))
			return false;

		if ( empty( $status_row ) )
			return false;

		/* own status */
		if ( $status_row['idUser'] == $action_user_id ) 
		{
			return false;
		}

		/* protected user */
		if ( $status_row['idUser'] && $user_row = JWDB_Cache_User::GetDbRowById( $status_row['idUser'] ) )
		{
			if ( self::IsProtected( $user_row, $action_user_id ) )
				return true;
		}

		/* protected conference */
		if ( $status_row['idConference'] )
		{
			$conference = JWConference::GetDbRowById( $status_row['idConference'] );
			if ( $conference && $conference_user = JWDB_Cache_User::GetDbRowById( $conference['idUser'] ) )
				return self::IsProtected( $conference_user, $action_user_id );
		}

		return false;
	}

	static public function IsProtected( $user_row, $action_user_id )
	{
		if( JWUser::IsAdmin($action_user_id ))
			return false;

		if ( empty( $user_row ) )
			return false;
		
		if ( $user_row['protected'] == 'Y' 
			&& $action_user_id != $user_row['id'] 
			&& false == JWFollower::IsFollower( $action_user_id, $user_row['id'] )
		)
		{
			return true;
		}
		
		return false;
	}

	static public function SetUserStatusPicture($user_id, $picture_id=null)
	{
		if ( null==$picture_id )
			return ;

		$status_ids = JWStatus::GetNonPictureStatusIdsFromUser( $user_id );
		foreach ( $status_ids as $status_id )
		{
			JWDB_Cache_Status::SetIdPicture( $status_id, $picture_id );
		}
	}

	static public function FilterStatus( $status, $extra_info=array() )
	{
		JWFilterConfig::Normal();
		if ( JWFilterRule::IsNeedFilter( $status ) )
		{
			$type = JWQuarantineQueue::T_STATUS;
			$user_id = $extra_info[0];
			$receiver_id = null;
			JWQuarantineQueue::Create( $user_id, $receiver_id, $type, $extra_info );
			return true;
		}
		return false;
	}

	static public function ParseVoteItem( $status )
	{
		if ( false == preg_match( '/(\s*({([^\{\}]+)\}\s*)+)$/iU', $status, $matches ) )
			return false;

		$status = preg_replace( '/(\s*({([^\{\}]+)\}\s*)+)$/i', '', $status );
		$matched = $matches[1];

		if ( false == preg_match_all( '/\s*{([^\{\}]+)\}\s*/iU', $matched, $matches ) )
			return false;
		
		if ( 1==count($matches[1]) )
			return false;

		return array( 
			'status' => $status,
			'items' => $matches[1],
		);
	}
}
?>
