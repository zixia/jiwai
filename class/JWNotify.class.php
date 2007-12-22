<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWNotify Class
 */
class JWNotify{
	
	/**
	 * 来自Web的Nudge消息，仅中转
	 */
	static public function NotifyWebNudge( &$queue ) 
	{
		if ( empty( $queue ) )
			return ;

		$metaInfo = $queue['metaInfo'];

		$idUsers = @$metaInfo['idUsers'];
		$message = @$metaInfo['message'];
		$messageType = @$metaInfo['messageType'];
		$source = @$metaInfo['source'];
		$options = @$metaInfo['options'];

		echo "[$queue[type]] idUsers: array(" . implode( ',', $idUsers ) . ")\n"; 

		if ( empty( $idUsers ) || $source != 'bot' )
			break;

		JWNudge::NudgeToUsers( $idUsers, $message, $messageType, $source, $options );
	}

	static public function NotifySmsInvite( &$queue ) 
	{
		if ( empty( $queue ) )
			return ;

		$metaInfo = $queue['metaInfo'];

		$idUserFrom = $queue['idUserFrom'];
		$message = $metaInfo['message'];
		$addressTo = $metaInfo['address'];
		$type = $metaInfo['type'];
		
		$deviceRow = JWDevice::GetDeviceDbRowByAddress( $addressTo, $type );

		if ( empty( $deviceRow ) ) 
		{
			$inviteCode = JWDevice::GenSecret( 8 );
			JWInvitation::Create($idUserFrom, $addressTo, $type, $message, $inviteCode);
		}
		$message .= (false===strpos($message, ' F ')) ? ' 请回复 F 确定' : '';

		$user = JWUser::GetUserInfo( $idUserFrom );
		$conference = JWConference::GetDbRowFromUser( $idUserFrom );

		if ( empty( $conference ) ) 
		{
			$serverAddress = JWFuncCode::GetCodeFunc( $addressTo, $idUserFrom, JWFuncCode::PRE_REG_INVITE );
		}
		else
		{
			$serverAddress = self::GetServerAddress( $addressTo, $conference, $user );
		}

		return JWRobot::SendMtRaw ( $addressTo, $type, $message, $serverAddress );
	}

	/**
	 * 手机邀请
	 */
	static public function NotifyInvite( &$queue ) 
	{
		if ( empty( $queue ) )
			return ;

		$metaInfo = $queue['metaInfo'];

		$idUserFrom = $queue['idUserFrom'];
		$message = $metaInfo['message'];
		$addressTo = $metaInfo['address'];
		$type = $metaInfo['type'];
		$webInvite = isset($metaInfo['webInvite'])  ? true : false;

		echo "[$queue[type]] idUserFrom: $idUserFrom, " . "type: $type, " . "addressTo: $addressTo\n"; 

		JWSns::Invite( $idUserFrom, $addressTo, $type, $message, $webInvite);
	}

	/**
	 * 一般更新的处理，含 MMS/STATUS/CONFERENCE
	 */
	static public function NotifyStatus( &$queue ) 
	{
		if ( empty( $queue ) )
			return ;

		$metaInfo = $queue['metaInfo'];
		$options = $metaInfo['options'];

		$idStatus = @$metaInfo['idStatus'];
		if ( $idStatus == null )
			return ;

		$status_row = JWDB_Cache_Status::GetDbRowById( $idStatus );
		if ( empty( $status_row ) )
			return ;

		$conference = $mms = $sender_user = $receiver_user = $conference_user = null;
		$tag = $thread_status = null;
		$is_protected_conference = false;

		/* sender_user */
		$sender_user = JWUser::GetDbRowById( $status_row['idUser'] );

		/* receiver_user */
		if ( $status_row['idUserReplyTo'] )
		{
			$receiver_user = JWUser::GetDbRowById( $status_row['idUserReplyTo'] );
		}
		
		/* conference_user and conference */
		if ( $status_row['idConference'] )
		{
			$conference = JWConference::GetDbRowById( $status_row['idConference'] );
			if ( false == empty( $conference ) && $conference['idUser'] )
			{
				$conference_user = JWUser::GetDbRowById( $conference['idUser'] );
				$options['idConference'] = $status_row['idConference'];
				$is_protected_conference = $conference['friendOnly'] == 'Y' 
						|| $conference_user['protected'] == 'Y';
			}
		}
		
		/* mms */
		if ( $status_row['isMms'] == 'Y' && $status_row['idPicture'] )
		{
			$mms = JWDB_Cache_Picture::GetDbRowById( $status_row['idPicture'] );
			$options['isMms'] = true;
		}

		/* tag */
		if ( $status_row['idTag'] )
		{
			$tag = JWTag::GetDbRowById( $status_row['idTag'] );
		}

		/* thread */
		if ( $status_row['idThread'] )
		{
			$thread_status = JWDB_Cache_Status::GetDbRowById( $status_row['idThread'] );
		}

		if ( false == isset( $options['notify'] ) )
		{
			$options['notify'] = 'ALL';
		}

		$pretty_options = array();
		if ( $tag ) 
		{
			$pretty_options = array( 'tag' => $tag['name'], );
		}
		
		/**
		 * build message
		 */
		$message = array(
			'im' => $status_row['raw_status'],
			'sms' => $status_row['raw_status'],
		);

		if ( $mms ) 
		{
			$mms_url = 'http://JiWai.de/'.$sender_user['nameUrl'].'/mms/'.$idStatus;
			$mms_plus_im = " 彩信<$mms[fileName]>地址：$mms_url";
			$mms_plus_sms = " 彩信<$mms[fileName]>地址：$mms_url";

			$message['im'] .= $mms_plus_im;
			$message['sms'] .= $mms_plus_sms;
		}


		if ( $receiver_user )
		{
			$message['im'] = "@$receiver_user[nameScreen] $message[im]";
			$message['sms'] = "@$receiver_user[nameScreen] $message[sms]";
		}

		/**
		 * Sync To Twitter, need transfer to other pool service
		 */
		$bindOther = JWBindOther::GetBindOther( $sender_user['id'] );
		if ( isset($bindOther['twitter']) || isset($bindOther['fanfou']) ) 
		{
			$queue_data = array(
				'device' => $status_row['device'],
				'message' => $message['im'],
				'not_reply' => null==$receiver_user,
				'not_conference' => null==$conference,
				'bind' => $bindOther,
			);

			$queue_instance = JWPubSub::Instance('spread://localhost/');
			$queue_instance->Publish('/statuses/bindother', $queue_data);
		}

		/* Add reply Link */
		/**
		if ( null==$conference )
		{
			if ( $thread_status )
			{
				$thread_user = JWUser::GetDbRowById( $thread_status['idUser'] );
				$reply_plus_url = ' 回复请到：http://JiWai.de/' . $thread_user['nameUrl']
						. '/thread/' . $thread_status['id'] . '/' . $idStatus;
			}
			else
			{

				$reply_plus_url = ' 回复请到：http://JiWai.de/' . $sender_user['nameUrl']
						. '/thread/' . $idStatus;
			}
			$message['im'] .= $reply_plus_url;
		}
		*/

		/** Sync to Facebook **/
		if ( null==$receiver_user && $idFacebook = JWFacebook::GetFBbyUser($sender_user['id']) ) 
		{
			JWFacebook::RefreshRef($sender_user['id']);
			$pic = JWPicture::GetUrlById( $status_row['idPicture'] , 'picture' );
			$picUrl = $mms ? $mms_url : null;
			$status = $status_row['status'];
			$device = $status_row['device'];
			JWFacebook::PublishAction($idFacebook, $sender_user['nameUrl'], $idStatus
					, $status_row['raw_status'], JWDevice::GetNameFromType($device), $pic, $picUrl );
		}
		/* */
		
		/** have_send_ids */
		$have_send_ids = array($sender_user['id']);

		$to_ids = array();
		if ( $receiver_user 
				&& false == JWBlock::IsBlocked($receiver_user['id'], $sender_user['id'] ) )
		{
			$to_ids = array( $receiver_user['id'] );	
			$message_send = array(
				'im' => self::GetPrettySender($sender_user, $pretty_options).': '.$message['im'],
				'sms' => self::GetPrettySender($sender_user, $pretty_options).': '.$message['sms'],
			);

			echo "[$queue[type]] idUserFrom: $sender_user[id], " . "idUserReplyTo: $receiver_user[id]\n"; 

			JWNudge::NudgeToUsers( $to_ids, $message_send, 'nudge', 'bot', $options );
			$have_send_ids = array_merge( $have_send_ids, $to_ids );
		}


		if ( $conference && $conference_user ) 
		{
			$to_ids = array();
			$to_ids = self::GetAvailableConferenceFollowerIds( $conference, $conference_user );
			$to_ids = self::GetFollowerIds( $to_ids, $options['notify'] );
			$to_ids = array_diff( $to_ids, $have_send_ids );

			$message_send = array(
				'im' => self::GetPrettySender($sender_user, $pretty_options)
					. "[$$conference_user[nameScreen]]: $message[im]",
				'sms' => self::GetPrettySender($conference_user, $pretty_options)
					. "[$$conference_user[nameScreen]]: $message[sms]",
			);
			
			echo "[$queue[type]] idUserFrom: $sender_user[id], idConference: $conference[id], "
				. "Followers: array("
				. Implode( ',', $to_ids ) . ")\n"; 

			JWNudge::NudgeToUsers( $to_ids, $message_send, 'nudge', 'bot', $options );
			$have_send_ids = array_merge( $have_send_ids, $to_ids );
		}
		
		/**
		 * 通知发送者的其他 Follower，需要考虑的是，发送者是会议用户本身，则不通知
		 */
		if ( false==$is_protected_conference
			&& ( null==$conference || false==($sender_user['id']==$conference_user['id']) ) )
		{
			$to_ids = array();
			$to_ids = self::GetAvailableFollowerIds( $sender_user['id'] );
			
			/* only follow both sender and receiver can got message */
			if ( $receiver_user && false==($receiver_user['id']==$sender_user['id']) )
			{
				$receiver_user_follower_ids = self::GetAvailableFollowerIds( $receiver_user['id'] );
				$to_ids = array_diff( $to_ids, array_diff($to_ids, $receiver_user_follower_ids) );
			}
			$to_ids = array_diff( $to_ids, $have_send_ids );

			$message_send = array(
				'im' => self::GetPrettySender($sender_user, $pretty_options).': '.$message['im'],
				'sms' => self::GetPrettySender($sender_user, $pretty_options).': '.$message['sms'],
			);

			echo "[$queue[type]] idUserFrom: $sender_user[id], idStatus: $idStatus, "
				. "Followers: array("
				. Implode( ',', $to_ids ) . ")\n"; 

			/**
			 * 注释下面这行，那么给好友的通知，如果是通过 SMS，将会带上会议特服号；
			 */
			$options['idConference'] = null;

			JWNudge::NudgeToUsers( $to_ids, $message_send, 'nudge', 'bot', $options );
			$have_send_ids = array_merge( $have_send_ids, $to_ids );
		}
		
		/* tag follower */		
		if (false==$is_protected_conference && $tag && null==$thread_status)
		{
			$to_ids = array();
			$to_ids = self::GetAvailableTagFollowerIds( $tag['id'], $sender_user['id'] );
			$to_ids = array_diff( $to_ids, $have_send_ids );

			$message_send = array(
				'im' => self::GetPrettySender($sender_user, $pretty_options).': '.$message['im'],
				'sms' => self::GetPrettySender($sender_user, $pretty_options).': '.$message['sms'],
			);

			echo "[TAG] idUserFrom: $sender_user[id], idStatus: $idStatus, idTag: $tag[id], "
				. "Followers: array("
				. Implode( ',', $to_ids ) . ")\n"; 

			JWNudge::NudgeToUsers( $to_ids, $message_send, 'nudge', 'bot', $options );
			$have_send_ids = array_merge( $have_send_ids, $to_ids );
		}

		/** 
		 * Track Notify [TEST ONLE]
		 * 仅当用户的更新为公开时，才转发给其他同学
		 **/
		if ( $sender_user['protected']=='N' && false==$is_protected_conference )
		{

			$to_ids = array();
			$message_be_cutted = $status_row['status'];
			$message_cutted = mb_substr( $message_be_cutted, 0, 420, 'UTF-8' );  //maybe block .....

			$trackword_sequence_id = JWTrackWord::GetStatusTrackOrder( $message_cutted );
			$to_ids = JWTrackUser::GetIdUsersBySequence( $trackword_sequence_id );

			$to_ids = array_diff( $to_ids, $have_send_ids );

			if ( false == empty( $to_ids ) )
			{
				echo "[TRACK] idUserFrom: $sender_user[id], idStatus: $idStatus, "
					. "Followers: array("
					. Implode( ',', $to_ids ) . ")\n"; 

				$message_send = '('
						. self::GetPrettySender($sender_user, $pretty_options)
						. '): '
						. $message_be_cutted;

				JWNudge::NudgeToUsers( $to_ids, $message_send, 'nudge', 'bot', $options );
				$have_send_ids = array_merge( $have_send_ids, $to_ids );
			}
		}
	}

	/**
	 * 考虑 Friend 关系 2007-09-20
	 * 考虑 Block 关系 2007-10-15
	 */
	static public function GetAvailableFollowerIds($user_id) 
	{
		$user_id = JWDB::CheckInt( $user_id );

		$follower_ids = JWFollower::GetNotificationIds( $user_id );
		
		$user_info = JWUser::GetUserInfo( $user_id );

		/* friend private */
		if ( $user_info['protected'] == 'Y' ) 
		{
			$friend_ids = JWFollower::GetFollowingIds( $user_id );
			$follower_ids = array_diff( $friend_ids, array_diff( $friend_ids, $follower_ids ) );
		}
		/* (who)s block idUser */
		$blocked_ids  = JWBlock::GetIdUsersByIdUserBlock( $user_id );
		if ( false == empty( $blocked_ids ) ) 
		{
			$follower_ids = array_diff( $follower_ids, $blocked_ids );
		}

		return $follower_ids;
	}

	/**
	 * 考虑 Friend 关系 2007-09-20
	 * 考虑 Block 关系 2007-10-15
	 */
	static public function GetAvailableConferenceFollowerIds($conference, $conference_user) 
	{
		$follower_ids = JWFollower::GetNotificationIds( $conference_user['id'] );
		
		/* friend private */
		if ( $conference_user['protected']=='Y' || $conference['friendOnly']=='Y' ) 
		{
			$friend_ids = JWFollower::GetFollowingIds( $conference_user['id'] );
			$follower_ids = array_diff( $friend_ids, array_diff( $friend_ids, $follower_ids ) );
		}
		/* (who)s block idUser */
		$blocked_ids  = JWBlock::GetIdUsersByIdUserBlock( $conference_user['id'] );
		if ( false == empty( $blocked_ids ) ) 
		{
			$follower_ids = array_diff( $follower_ids, $blocked_ids );
		}

		return $follower_ids;
	}

	/**
	 * 考虑 Friend 关系 2007-09-20
	 * 考虑 Block 关系 2007-10-15
	 */
	static public function GetAvailableTagFollowerIds($tag_id, $user_id) 
	{
		$tag_id = JWDB::CheckInt( $tag_id );
		$user_id = JWDB::CheckInt( $user_id );

		$follower_ids = JWTagFollower::GetNotificationIds( $tag_id );
		
		$user_info = JWUser::GetUserInfo( $user_id );

		/* friend private */
		if ( $user_info['protected'] == 'Y' ) 
		{
			$friend_ids = JWFollower::GetFollowingIds( $user_id );
			$follower_ids = array_diff( $friend_ids, array_diff( $friend_ids, $follower_ids ) );
		}
		/* (who)s block idUser */
		$blocked_ids  = JWBlock::GetIdUsersByIdUserBlock( $user_id );
		if ( false == empty( $blocked_ids ) ) 
		{
			$follower_ids = array_diff( $follower_ids, $blocked_ids );
		}

		return $follower_ids;
	}

	/**
	 * 按条件重新筛选Follower_Ids
	 */
	static public function GetFollowerIds($follower_ids, $type='IM')
	{

		if ( empty( $follower_ids ) )
			return array();

		$type = strtoupper( $type );

		switch( $type )
		{
			case 'IM':
				$condition = "deviceSendVia IN ('msn','gtalk','skype','aol','qq','yahoo','fetion')";
			break;
			case 'Y':
			case 'ALL':
				return $follower_ids;
			default:
				return array();
		}
		
		$condition_string = implode(',', $follower_ids);
		$sql = "SELECT id FROM User WHERE $condition AND id IN ($condition_string)";

		$rows = JWDB::GetQueryResult( $sql, true );
		if ( empty($rows) )
			return array();
		
		$rtn = array();
		foreach ( $rows as $r ) 
		{
			array_push( $rtn, $r['id'] );
		}

		return array_unique( $rtn );
	}

	/**
	 * 获取会议号；
	 */
	static public function GetServerAddress( $mobileNo, $conference, $user ) 
	{

		$code = JWSPCode::GetCodeByMobileNo( $mobileNo );
		if ( empty( $code ) )
			return null;

		if ( empty( $conference ) || empty($user) )
			return $code['code'] . $code['func'] . $code['funcPlus'];

		if ( preg_match( '/^gp(\d{6})$/i', $user['nameScreen'], $matches ) ) 
		{
			return $code['code'] . $code['func'] . JWFuncCode::PRE_STOCK_CODE . $matches[1];
		}

		if ( preg_match( '/^gp(\d{3})$/i', $user['nameScreen'], $matches ) ) 
		{
			return $code['code'] . $code['func'] . JWFuncCode::PRE_STOCK_CATE . $matches[1];
		}

		if ( $conference['number'] !== null )
			return $code['code'] . $code['func'] . JWFuncCode::PRE_CONF_CUSTOM . $conference['number'];

		return $code['code'] . $code['func'] . JWFuncCode::PRE_CONF_IDUSER . $conference['idUser'];
	}
	
	/**
	 * Get Pretty Sender
	 */
	static public function GetPrettySender( &$userSender, $options=array() ) 
	{
		if ( isset( $options['tag'] ) ) 
		{
			return $userSender['nameScreen'].'[#'.$options['tag'].']';
		}
		return $userSender['nameScreen'];
	}
}
?>
