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
	
		
		JWNudge::NudgeUserIds(	 array($idUserReceiver)
								,"$sender_row[nameScreen]: $message "
									."（可直接回复“"
									."D $sender_row[nameScreen] 你想说的悄悄话"
									."”"
									."）"
								,'direct_messages'
							);

		return true;
	}


	// 先提出来，以后可能又相关操作
	static public function CreateUser($userRow)
	{
		$user_id = JWUser::Create($userRow);
		JWBalloonMsg::CreateUser($user_id);

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
	
		if ( ! JWFriend::Create($userRow['id'], $friendRow['id']) )
			throw new JWException('JWFriend::Create failed');

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
		$friend_request_id = JWFriendRequest::Create($idUser, $idFriend);
		JWBalloonMsg::CreateFriendRequest($idUser,$idFriend, $friend_request_id);

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
		$id_invite		= JWInvitation::Create($idUser,$address,$type,$email_message, $code_invite);

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
			case 'sms':
			case 'qq':
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
		$num_follower	= JWFollower::GetFollowerNum($idUser);

		//$num_status		= JWStatus::GetStatusNum($idUser);
		$num_status		= JWDB_Cache_Status::GetStatusNum($idUser);

		return array(	'pm'			=> $num_pm
						, 'fav'			=> $num_fav
						, 'friend'		=> $num_friend
						, 'follower'	=> $num_follower
						, 'status'		=> $num_status
					);
	}
	
	/**
	 * 获取用户特服号后缀
	 * @param $idUser int
	 * @param $sendDevice string default 'web'
	 * @param $own boolean default false;
	 */
	static public function GetSmsSuffix($idUser, $idUserReplyTo, $sendDevice='web' ){
		$idUser = JWDB::CheckInt( $idUser );
		$idUserReplyTo = JWDB::CheckInt( $idUserReplyTo );

		$userInfo = JWUser::GetUserInfo( $idUserReplyTo );
		if( empty( $userInfo )  || $userInfo['idConference'] == null )
			return array();

		$conference = JWConference::GetDbRowById( $userInfo['idConference'] );
		if( empty( $conference ) )
			return array();

		if( $conference['friendOnly'] == 'Y' ) {
			if( false == JWFriend::IsFriend($idUserReplyTo, $idUser) ) {
				return array();
			}
		}

		$smssuffix = ( $conference['number'] == null ) ? "99$userInfo[id]" : "1$conference[number]";

		if( $idUser === $idUserReplyTo )
			return array(
					'idConference' => $userInfo['idConference'],
					'smssuffix' => $smssuffix,
				    );
		
		switch($sendDevice){
			case 'web':
				$deviceType = 'web';
			break;
			case 'sms':
				$deviceType = 'sms';
			break;
			default:
				$deviceType = 'im';
		}	
		
		$deviceAllow = explode(',', $conference['deviceAllow'] );
		if( in_array( $deviceType, $deviceAllow ) )
			return array(
					'idConference' => $userInfo['idConference'],
					'smssuffix' => $smssuffix,
				    );
		return array();
	}

	/**
	 * 获取用户特服号后缀
	 * @param $idUser int
	 * @param $sendDevice string default 'web'
	 * @param $own boolean default false;
	 */
	static public function GetReplyTo($idUser, $serverAddress, $type='sms'){
		switch( $type ){
			case 'sms':
				/*
				 * add 2007-08-08 for sms number alias
				 */
				if( isset( JWConference::$smsAlias[ $serverAddress ] ) ){
					$serverAddress = JWConference::$smsAlias[ $serverAddress ];
				}
				if( preg_match("/[0-9]{8}(99|1)(\d+)/", $serverAddress, $matches ) ) {
					$normalMeeting = $matches[1] == 99 ? true : false;
					$userInfo = $conference = null;
					if( $normalMeeting ){
						$userInfo = JWUser::GetUserInfo( $matches[2] );
						$conference = JWConference::GetDbRowFromUser( $matches[2] ) ;
					}else{
						$conference = JWConference::GetDbRowFromNumber( $matches[2] );
						if( !empty( $conference ) ){
							$userInfo = JWUser::GetUserInfo( $conference['id'] );
						}
					}
					
					//
					if( $conference && $userInfo && $userInfo['idConference']) {
						if( $conference['friendOnly'] == 'Y' 
							&& false == JWFriend::IsFriend($userInfo['id'], $idUser) ) {
							$reply_info = array(
								'status_id' => null,
								'user_id' => $userInfo['id'],
								'smssuffix' => null,
								'idConference' => null,
								);
						}else{
							$reply_info = array(
								'status_id' => null,
								'user_id' => $userInfo['id'],
								'smssuffix' => $conference['number']==null ? 
										"99$userInfo[id]" : "1$conference[number]",
								'idConference' => $userInfo['idConference'],
								);
						}
						return $reply_info;
					}
				}
			break;
			default:
			break;
		}
		$userInfo = JWUser::GetUserInfo( $idUser );
		if( empty( $userInfo ) || null==$userInfo['idConference'] )
			return array();

		$conference = JWConference::GetDbRowById( $userInfo['idConference'] );
		if( empty( $conference ) )
			return array();

		$reply_info = array(
				'status_id' => null,
				'user_id' => $idUser,
				'smssuffix' => $conference['number']==null ? 
						"99$userInfo[id]" : "1$conference[number]",
				'idConference' => $userInfo['idConference'],
				);
	}

	/*
	 * 更新用户叽歪嘻嘻，并进行广播
	 */
	static public function	UpdateStatus( $idUser, $status, $device='web', $time=null, $isSignature='N', $serverAddress=null)
	{
		//For remove \n\r
		$status = preg_replace('/[\n\r]/' ,' ', $status);

		/*
		 * check signature change
		 */
		if( 'Y' == $isSignature ) {
			if( false == JWDevice::IsSignatureChanged($idUser, $device, $status)){
				return true;
			}
		}

		$statusPost = JWRobotLingo::ConvertCorner( $status );
		$reply_info	= JWStatus::GetReplyInfo($statusPost);	

		if( !empty( $reply_info )) {
			$status = $statusPost;
		}

		/*
		 *  判断是否需要Filter，如果需要进入status
		 *
		 */
		if( true && false )  // 暂时不升级这快，影响较大
		{
			$idReciever = empty( $reply_info ) ? null : $reply_info['user_id'];
			$status = empty( $reply_info ) ? $status : $statusPost;

			JWFilterConfig::Normal();
			if( JWFilterRule::IsNeedFilter($status, $idUser, $idReciever, $device) ){
				JWStatusQuarantine::Create($idUser,$status,$device,$time, $isSignature);
				return true;
			}
		}
		/* 
		 * 获得用户自动发给会议特服号的 回复信息，在Status::Create时，需要加上
		 * 构建新的 idUser , 结构 "idUser:idUserReplyTo", 在 JWStatus::Create时，可以解析
		 * Modified 2007/07/29
		 */
		$processInfo = JWSns::ProcessStatusNotify( $idUser, $status, $reply_info, $device, $serverAddress );
		$oldIdUser = $idUser;
		if( $processInfo['reply'] ) {
			$idUser = "$idUser:$processInfo[reply]";
		}
		$ret = JWStatus::Create($idUser,$status,$device,$time, $isSignature );

		//added 2007-07-29
		$ret = array( 'op' => $ret, 'reply' => null, );
		if( $ret['op'] ) {
			JWStatusNotifyQueue::Create($oldIdUser, $ret['op'], time(), $processInfo['notify'] );
			if( $processInfo['notify']['idConference'] ) {
				$conference = JWConference::GetDbRowById( $processInfo['notify']['idConference'] );
				if( false == empty($conference) && $conference['msgUpdateStatus'] ){
					$userInfo = JWUser::GetUserInfo( $oldIdUser );
					$ret['reply'] = "$userInfo[nameScreen]，$conference[msgUpdateStatus]" ;
				}
			}
		}
		
		//Refresh facebook profile if necessary
		if ( JWFacebook::Verified( $oldIdUser ) ) JWFacebook::RefreshRef($idUser);
		return $ret;
	}


	static public function ProcessStatusNotify($idUser, $status=null, $reply_info=array(), $device='web', $serverAddress=null){
		$idUser = JWDB::CheckInt( $idUser );
		/*
		 * 判断是否是会议模式相关，目前仅可以从SMS特服号上取出 会议id
		 * 如果 reply_info 中含有 smssuffix 则一定为会议用户
		 */
		$notifyInfo = array(
			'device' => $device,
			'status' => $status,
			'serverAddress' => $serverAddress,
			'idConference' => null,
		);
		if( !empty($reply_info) ) {
			$notifyInfo['idUserReplyTo'] = $reply_info['user_id'];
			$suffixInfo = JWSns::GetSmsSuffix($idUser, $reply_info['user_id'] , $device );
			$reply_info['smssuffix'] = empty($suffixInfo) ? null : $suffixInfo['smssuffix'];
			$notifyInfo['idConference'] = empty($suffixInfo) ? null : $suffixInfo['idConference'];
		    	if( $reply_info['smssuffix'] == null ) {
				$reply_info['user_id'] = null;
			}
		}

		if( empty($reply_info) ) {
			$reply_info = JWSns::GetReplyTo( $idUser, $serverAddress, $device );
			$notifyInfo['idUserReplyTo'] = empty( $reply_info ) ? null : $reply_info['user_id'];
			$notifyInfo['idConference'] =  empty($reply_info ) ? null : $reply_info['idConference'];
		}

		$smssuffix = empty( $reply_info ) ? null : $reply_info['smssuffix'];
		$idUserReplyTo = empty( $reply_info ) ? null : $reply_info['user_id'];

		/**    Commented By shwdai@gmail.com 2007/07/29
		//Notify Followers
		JWSns::NotifyFollower( $idUser, $idUserReplyTo, $status, $smssuffix );
		**/

		/*
		* record  notify information
		*/
		$notifyInfo['smssuffix'] = $smssuffix;

		$returnArray = array(
			'reply' => $idUserReplyTo,
			'notify' => $notifyInfo,
		);

		return $returnArray;
	}

	/**
	 * Nudge updates to follower,consider conference model
	 * @param $idSender int
	 * @param $idUserReplyTo int
	 * @param $status string
	 * @param $smssuffix 
	 */
	static public function NotifyFollower( $idSender, $idUserReplyTo=null, $status=null, $smssuffix=null ){
		$idSender = JWDB::CheckInt( $idSender );

		if( $smssuffix ) {
			//2007-08-07
			//$status = preg_replace("/^\s?@\s?\w+/", "", $status );
			/*
			$userInfo = JWUser::GetUserInfo( $idUserReplyTo );
			$status = "$userInfo[nameScreen]: $status";
			$follower_ids = JWFollower::GetFollowerIds($idUserReplyTo);
			*/
			/*
			 * conference notify enhance 2007-08-06
			*/
			$userInfo = JWUser::GetUserInfo( $idSender );
			$status = "$userInfo[nameScreen]: $status";
			$follower_ids = JWFollower::GetFollowerIds($idUserReplyTo);
			settype( $follower_ids , 'array' );

			/** 2007-08-20 */
			$follower_ids = array_diff( $follower_ids, array( $idSender ) );
			if( false == ($idSender == $idUserReplyTo ) ){
				array_push( $follower_ids, $idUserReplyTo );
			}

		}else if( null == $idUserReplyTo ) {
			$userInfo = JWUser::GetUserInfo( $idSender );
			$follower_ids = JWFollower::GetFollowerIds($idSender);
			$status = "$userInfo[nameScreen]: $status";
		}else{
			$userInfo = JWUser::GetUserInfo( $idSender );
			$follower_ids = array( $idUserReplyTo ) ;
			$status = "$userInfo[nameScreen]: $status";
		}

		if( empty( $follower_ids ) ) 
			return true;

		return JWNudge::NudgeUserIds( $follower_ids, $status );
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

}
?>
