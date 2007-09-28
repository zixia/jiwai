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

		$to_ids = array();
		if( $idUserTo )
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
			$follower_ids = JWFollower::GetFollowerIds( $idUserConference );
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
		if( false == ( $idUserTo || $idUserFrom == $idUserConference ) ) 
		{
			$userSender = JWUser::GetUserInfo( $idUserFrom );
			$messageObject = is_array( $message ) ? 
				$message : self::GetPrettySender($userSender).': '.$message;

			$sender_follower_ids = JWFollower::GetFollowerIds( $idUserFrom );
			$sender_follower_ids = array_diff( $sender_follower_ids, $follower_ids );
			$sender_follower_ids = array_diff( $sender_follower_ids, array($idUserFrom) );

			echo "[$queue[type]] idUserFrom: $idUserFrom, idStatus: $idStatus, "
				. "Followers: array("
				. Implode( ',', $sender_follower_ids ) . ")\n"; 

			/**
			 * 注释下面这行，那么给好友的通知，如果是通过 SMS，将会带上会议特服号；
			 */
			$options['idConference'] = null;

			JWNudge::NudgeToUsers( $sender_follower_ids, $messageObject, 'nudge', 'bot', $options );
		}
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
				$condition = "deviceSendVia IN ('msn','gtalk','skype','qq')";
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
		if( strtoupper($userSender['nameScreen']) == strtoupper($userSender['nameFull']) )
			return $userSender['nameScreen'];

		return $userSender['nameFull'].'('. $userSender['nameScreen'] . ')';
	}
}
?>
