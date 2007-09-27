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

		if( $idUserTo )
		{
			$follwer_ids = array( $idUserTo );	

			$userSender = JWUser::GetUserInfo( $idUserFrom );
			$message = is_array( $message ) ? 
					$message : self::GetPrettySender($userSender).': '.$message;

			echo "[$queue[type]] idUserFrom: $idUserFrom, " . "idUserReplyTo: $idUserTo\n"; 

			JWNudge::NudgeToUsers( $follwer_ids, $message, 'nudge', 'bot', $options );
		}else{
			$follwer_ids = array();

			if( $idUserConference ) {

				$follwer_ids = JWFollower::GetFollowerIds( $idUserConference );
				$follwer_ids = self::GetFollowerIds( $follwer_ids, $options['notify'] );
				
				$userConference = JWUser::GetUserInfo( $idUserConference );
				$message = is_array( $message ) ? 
					$message : self::GetPrettySender($userConference).': '.$message;

				echo "[$queue[type]] idUserFrom: $idUserFrom, idStatus: $idStatus, "
					. "Followers: array("
					. Implode( ',', $follwer_ids ) . ")\n"; 
				echo "[$queue[type]] idUserFrom: $idUserFrom, idUserConference: $idUserConference, "
					. "Followers: array("
					. Implode( ',', $follwer_ids ) . ")\n"; 

				JWNudge::NudgeToUsers( $follwer_ids, $message, 'nudge', 'bot', $options );
			}

			$userSender = JWUser::GetUserInfo( $idUserFrom );
			$message = is_array( $message ) ? 
				$message : self::GetPrettySender($userSender).': '.$message;

			$sender_follower_ids = JWFollower::GetFollowerIds( $idUserFrom );
			$sender_follower_ids = array_diff( $sender_follower_ids, $follwer_ids );

			echo "[$queue[type]] idUserFrom: $idUserFrom, idStatus: $idStatus, "
				. "Followers: array("
				. Implode( ',', $sender_follower_ids ) . ")\n"; 

			JWNudge::NudgeToUsers( $sender_follower_ids, $message, 'nudge', 'bot', $options );
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

		if( preg_match( '/^gp(\d+)$/i', $user['nameScreen'], $matches ) ) {
			return $code['code'] . $code['func'] . JWFuncCode::PRE_STOCK_CODE . $matches[1];
		}

		if( preg_match( '/^stock_([0-9a-z]{3,8})$/i', $user['nameScreen'] ) ) {
			$number = $conference['number'];
			if( $number !== null ) {
				return $code['code'] . $code['func'] . JWFuncCode::PRE_STOCK_CATE . $number;
			}
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
