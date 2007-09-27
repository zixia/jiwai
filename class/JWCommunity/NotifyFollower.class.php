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
	static public function NotifyFollower($idStatus, $to='ALL', $options=array() )
	{
		$idStatus = JWDB::CheckInt( $idStatus );
		$statusInfo = JWStatus::GetDbRowById( $idStatus );

		if( empty($statusInfo) || null == $statusInfo['idConference'] )
			return true;

		$conference = JWConference::GetDbRowById( $statusInfo['idConference'] );
		$idUserConference = $conference['idUser'];
		$userConference = JWUser::GetUserInfo( $idUserConference );
		$idUserSender = $statusInfo['idUser'];
		$message = "$userConference[nameScreen]: $statusInfo[status]";

		$idUserToArray = JWFollower::GetFollowerIds( $idUserConference );
		$idUserToArray = array_diff( $idUserToArray, array($idUserSender) );
		$idUserToArray = self::GetFollowerIds( $idUserToArray , $to );


		if( empty( $idUserToArray ) )
			return true;

		$metaInfo = array(
			'message' => $message,
			'idUserToArray' => $idUserToArray,
			'options' => array(
						'idConference' => $statusInfo['idConference'],
						'idStatus' => $idStatus,
			),
				
		);

		return JWNotifyQueue::Create( null, null, JWNotifyQueue::T_CONFERENCE, $metaInfo );
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
}
?>
