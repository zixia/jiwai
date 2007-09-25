<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWCommunity_NotifyFollower Class
 */
class JWCommunity_NotifyFollower{
	/**
	 * 通知会议用户的follower，按一定级别
	 */
	static public function NotifyFollower($idStatus, $to='all')
	{
		$idStatus = JWDB::CheckInt( $idStatus );
		$statusInfo = JWStatus::GetDbRowById( $idStatus );

		if( empty($statusInfo) || null == $statusInfo['idConference'] )
			return true;

		$idUserConference = $statusInfo['idConference'];
		$userConference = JWUser::GetUserInfo( $idUserConference );
		$idUserSender = $statusInfo['idUser'];
		$message = "$userConference[nameScreen]: $statusInfo[status]";

		switch($to){
			case 'all':
			{
				$idUserToArray = JWFollower::GetFollowerIds( $idUserConference );
				$idUserToArray = array_diff( $idUserToArray, array($idUserSender) );
			}
			break;
			case 'sms':
			{
				$idUserToArray = JWFollower::GetFollowerIds( $idUserConference );
				$idUserToArray = array_diff( $idUserToArray, array($idUserSender) );
				$idUserToArray = self::GetFollowerIds( $idUserToArray , $to);
			}
			break;
			case 'im':
			{
				$idUserToArray = JWFollower::GetFollowerIds( $idUserConference );
				$idUserToArray = array_diff( $idUserToArray, array($idUserSender) );
				$idUserToArray = self::GetFollowerIds( $idUserToArray , $to );
			}
			default:
				return true;
		}

		if( empty( $idUserToArray ) )
			return true;

		$metaInfo = array(
			'message' => $message,
			'idUserToArray' => $idUserToArray,
		);

		return JWNotifyQueue::Create( null, null, JWNotifyQueue::T_CONFERENCE, $metaInfo );
	}
	
	/**
	 * 按条件重新筛选Follower_Ids
	 */
	static public function GetFollowerIds($follower_ids, $type='sms'){

		if( empty( $follower_ids ) )
			return array();

		switch( $type ){
			case 'sms':
				$condition = "deviceSendVia='sms'";
			break;
			case 'im':
				$condition = "deviceSendVia IN ('msn','gtalk','skype','qq')";
			break;
			case 'all':
				return $follower_ids;
			case 'web':
				return array();
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
}
?>
