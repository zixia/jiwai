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
	static public function NotifyWebNudge( &$queue ) {
		if( empty( $queue ) )
			return ;

		$metaInfo = $queue['metaInfo'];

		$idUsers = @$metaInfo['idUsers'];
		$message = @$metaInfo['message'];
		$messageType = @$metaInfo['messageType'];
		$source = @$metaInfo['source'];
		$options = @$metaInfo['options'];

		echo "[$queue[type]] idUsers: array(" . implode( ',', $idUsers ) . ")\n"; 

		if( empty( $idUsers ) || $source != 'bot' )
			break;

		JWNudge::NudgeToUsers( $idUsers, $message, $messageType, $source, $options );
	}

	static public function NotifySmsInvite( &$queue ) {
		if( empty( $queue ) )
			return ;

		$metaInfo = $queue['metaInfo'];

		$idUserFrom = $queue['idUserFrom'];
		$message = $metaInfo['message'];
		$addressTo = $metaInfo['address'];
		$type = $metaInfo['type'];
		
		$deviceRow = JWDevice::GetDeviceDbRowByAddress( $addressTo, $type );

		if( empty( $deviceRow ) ) {
			$inviteCode = JWDevice::GenSecret( 8 );
			JWInvitation::Create($idUserFrom, $addressTo, $type, $message, $inviteCode);
		}
		$message .= empty( $deviceRow ) ? ' 请回复你想要的用户名' : ' 请回复 F 确定';

		$user = JWUser::GetUserInfo( $idUserFrom );
		$conference = JWConference::GetDbRowFromUser( $idUserFrom );

		if( empty( $conference ) ) {
			$serverAddress = JWFuncCode::GetCodeFunc( $addressTo, $idUserFrom, JWFuncCode::PRE_REG_INVITE );
		}else{
			$serverAddress = self::GetServerAddress( $addressTo, $conference, $user );
		}

		return JWRobot::SendMtRaw ( $addressTo, $type, $message, $serverAddress );
	}

	/**
	 * 手机邀请
	 */
	static public function NotifyInvite( &$queue ) {
		if( empty( $queue ) )
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
	static public function NotifyStatus( &$queue ) {
		if( empty( $queue ) )
			return ;

		$idUserFrom = $queue['idUserFrom'];
		$idUserTo = $queue['idUserTo'];
		$metaInfo = $queue['metaInfo'];

		$message = @$metaInfo['message'];
		$options = @$metaInfo['options'];

		$idStatus = @$options['idStatus'];
		$idUserConference = @$options['idUserConference'];

		if( false == isset( $options['notify'] ) ){
			$options['notify'] = 'ALL';
		}

		$userSender = JWUser::GetUserInfo( $idUserFrom );

		/**
		 * Sync To Twitter, need transfer to other pool service
		 */
		$bindOther = JWBindOther::GetBindOther( $idUserFrom );
		if( isset($bindOther['twitter']) || isset($bindOther['fanfou']) ) {
			
			$status_row = JWDB_Cache_Status::GetDbRowById( $idStatus );
			if ( false == empty( $status_row ) && $status_row['device'] != 'api' ) {
				$messageObject = is_array($message) ? 
					( isset($message['im']) ? $message['im'] : null ) : $message;
				if( $messageObject ) {
					if( isset($bindOther['twitter']) )
						JWBindOther::PostStatus( $bindOther['twitter'], $message );
					if( isset($bindOther['fanfou']) )
						JWBindOther::PostStatus( $bindOther['fanfou'], $message );
				}
			}
		}

		$to_ids = array();
		if( $idUserTo && false == JWBlock::IsBlocked($idUserTo, $idUserFrom) )
		{
			$to_ids = array( $idUserTo );	

			$messageObject = is_array( $message ) ? 
					$message : self::GetPrettySender($userSender).': '.$message;

			echo "[$queue[type]] idUserFrom: $idUserFrom, " . "idUserReplyTo: $idUserTo\n"; 

			JWNudge::NudgeToUsers( $to_ids, $messageObject, 'nudge', 'bot', $options );
		}


		$follower_ids = array();
		if( $idUserConference ) 
		{
			$follower_ids = self::GetAvailableFollowerIds( $idUserConference );
			$follower_ids = self::GetFollowerIds( $follower_ids, $options['notify'] );
			$follower_ids = array_diff( $follower_ids, array($idUserFrom) );
			$follower_ids = array_diff( $follower_ids, $to_ids );
			
			$userConference = JWUser::GetUserInfo( $idUserConference );
			$messageObject = is_array( $message ) ? 
				$message : self::GetPrettySender($userConference) 
						.  ( $idUserConference == $idUserFrom ? '' : "[$userSender[nameScreen]]" ) 
						.  ": $message";


			echo "[$queue[type]] idUserFrom: $idUserFrom, idUserConference: $idUserConference, "
				. "Followers: array("
				. Implode( ',', $follower_ids ) . ")\n"; 

			JWNudge::NudgeToUsers( $follower_ids, $messageObject, 'nudge', 'bot', $options );
		}
		
		/**
		 * 只有 没有 idUserTo 才通知 idUserFrom 的 Follower
		 * 通知发送者的其他 Follower，需要考虑的是，发送者是会议用户本身，则不通知
		 */
		$sender_follower_ids = array();
		//if( false == ( $idUserTo || $idUserFrom == $idUserConference ) ) 
		if( false == ( $idUserFrom == $idUserConference ) ) 
		{
			$userSender = JWUser::GetUserInfo( $idUserFrom );
			$messageObject = is_array( $message ) ? 
				$message : self::GetPrettySender($userSender).': '.$message;

			$sender_follower_ids = self::GetAvailableFollowerIds( $idUserFrom );
			$sender_follower_ids = array_diff( $sender_follower_ids, $follower_ids );
			$sender_follower_ids = array_diff( $sender_follower_ids, array($idUserTo) );

			if( $idUserTo && $idUserTo != $idUserFrom ) {
				$to_user_follower_ids = self::GetAvailableFollowerIds( $idUserTo );
				$sender_follower_ids = array_diff( $sender_follower_ids, 
						array_diff( $sender_follower_ids, $to_user_follower_ids ) );
			}

			if( $idUserTo == $idUserFrom ) {
				$sender_follower_ids = array_diff( $sender_follower_ids, array($idUserFrom) );
			}

			echo "[$queue[type]] idUserFrom: $idUserFrom, idStatus: $idStatus, "
				. "Followers: array("
				. Implode( ',', $sender_follower_ids ) . ")\n"; 

			/**
			 * 注释下面这行，那么给好友的通知，如果是通过 SMS，将会带上会议特服号；
			 */
			$options['idConference'] = null;

			JWNudge::NudgeToUsers( $sender_follower_ids, $messageObject, 'nudge', 'bot', $options );
		}

		/** 
		 * Track Notify [TEST ONLE]
		 * 仅当用户的更新为公开时，才转发给其他同学
		 **/
		if( is_string($message) && $userSender['protected'] == 'N' ) {

			$messageCut = mb_substr( $message, 0, 420, 'UTF-8' );  //maybe block .....

			$idTrackWordSequence = JWTrackWord::GetStatusTrackOrder( $messageCut );
			$tracker_ids = JWTrackUser::GetIdUsersBySequence( $idTrackWordSequence );

			$tracker_ids = array_diff( $tracker_ids, $sender_follower_ids );
			$tracker_ids = array_diff( $tracker_ids, $follower_ids );
			$tracker_ids = array_diff( $tracker_ids, array( $idUserFrom ) );
			$tracker_ids = array_diff( $tracker_ids, array( $idUserTo ) );

			if( false == empty( $tracker_ids ) ){
				echo "[TRACK] idUserFrom: $idUserFrom, idStatus: $idStatus, "
					. "Followers: array("
					. Implode( ',', $tracker_ids ) . ")\n"; 

				$messageObject = '('.$userSender['nameScreen'].'): '.$message;
				JWNudge::NudgeToUsers( $tracker_ids, $messageObject, 'nudge', 'bot', $options );
			}
		}

	}

	/**
	 * 考虑 Friend 关系 2007-09-20
	 * 考虑 Block 关系 2007-10-15
	 */
	static public function GetAvailableFollowerIds($idUser) {
		$idUser = JWDB::CheckInt( $idUser );

		$followerIds = JWFollower::GetNotificationIds( $idUser );
		
		$userInfo = JWUser::GetUserInfo( $idUser );

		/* friend private */
		if( $userInfo['protected'] == 'Y' ) {
			$friendIds = JWFollower::GetFollowingIds( $idUser );
			$followerIds = array_diff( $friendIds, array_diff( $friendIds, $followerIds ) );
		}
		/* (who)s block idUser */
		$blockedIds  = JWBlock::GetIdUsersByIdUserBlock( $idUser );
		if( false == empty( $blockUserIds ) ) {
			$followerIds = array_diff( $followerIds, $blockedIds );
		}

		return $followerIds;
	}

	/**
	 * 按条件重新筛选Follower_Ids
	 */
	static public function GetFollowerIds($follower_ids, $type='IM'){

		if( empty( $follower_ids ) )
			return array();

		$type = strtoupper( $type );

		switch( $type ){
			case 'IM':
				$condition = "deviceSendVia IN ('msn','gtalk','skype','qq','yahoo')";
			break;
			case 'Y':
			case 'ALL':
				return $follower_ids;
			default:
				return array();
		}
		
		$idCondition = implode(',', $follower_ids);
		$sql = "SELECT id FROM User WHERE $condition AND id IN ($idCondition)";

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty($rows) )
			return array();
		
		$rtn = array();
		foreach( $rows as $r ) {
			array_push( $rtn, $r['id'] );
		}

		return array_unique( $rtn );
	}

	/**
	 * 获取会议号；
	 */
	static public function GetServerAddress( $mobileNo, $conference, $user ) {

		$code = JWSPCode::GetCodeByMobileNo( $mobileNo );
		if( empty( $code ) )
			return null;

		if( empty( $conference ) || empty($user) )
			return $code['code'] . $code['func'] . $code['funcPlus'];

		if( preg_match( '/^gp(\d{6})$/i', $user['nameScreen'], $matches ) ) {
			return $code['code'] . $code['func'] . JWFuncCode::PRE_STOCK_CODE . $matches[1];
		}

		if( preg_match( '/^gp(\d{3})$/i', $user['nameScreen'], $matches ) ) {
			return $code['code'] . $code['func'] . JWFuncCode::PRE_STOCK_CATE . $matches[1];
		}

		if( $conference['number'] !== null )
			return $code['code'] . $code['func'] . JWFuncCode::PRE_CONF_CUSTOM . $conference['number'];

		return $code['code'] . $code['func'] . JWFuncCode::PRE_CONF_IDUSER . $conference['idUser'];
	}
	
	/**
	 * Get Pretty Sender
	 */
	static public function GetPrettySender( &$userSender ) {
		return $userSender['nameScreen'];
		if( strtoupper($userSender['nameScreen']) == strtoupper($userSender['nameFull']) )
			return $userSender['nameScreen'];

		return $userSender['nameFull'].'('. $userSender['nameScreen'] . ')';
	}
}
?>
