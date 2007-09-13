<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Conference Class
 */
class JWConference {
	/**
	 * Instance of this singleton
	 *
	 * @var JWConference
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWConference
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}

	/**
	 * Get idConference from idUser,idUserReplyTo,serverAddress
	 */
	static public function FetchConference( $idSender,$idReceiver=null,$device='sms',$serverAddress=null,$address=null) {
		$idSender = JWDB::CheckInt( $idSender );
	
		//发送者是开启了会议模式用户	
		$userSender = JWUser::GetUserInfo( $idSender );
		if( empty($userSender) ) {
			return array();
		}

		if( $userSender['idConference'] ){ // Simple...
			return self::GetDbRowById( $userSender['idConference'] );
		}

		//会议用户信息
		$userInfo = $conference = null;

		//优先特服号分析
		if( $device == 'sms' ){
			$parseInfo = JWFuncCode::FetchConference( $serverAddress, $address );
			if( false == empty( $parseInfo ) ){
				$userInfo = $parseInfo['user'];
				$conference = $parseInfo['conference'];
			}
		}

		//如果从特服号中分析不出，再分析 idUserReplyTo 
		if( $idReceiver && ( empty($userInfo) || empty($conference) ) ) {
			$userInfo = JWUser::GetUserInfo( $idReceiver );
			if( false == empty( $userInfo ) && $userInfo['idConference'] ) {
				$conference = self::GetDbRowById( $userInfo['idConference'] );
			}
		}

		//分析 设备类型，好友允许设置
		if( false == empty( $userInfo ) && false == empty( $conference ) ) {
			$deviceCategory = JWDevice::GetDeviceCategory( $device );
			$allowedDevice = $conference['deviceAllow'];
			if( in_array( $deviceCategory, explode(',', $allowedDevice) ) ){
				if( $conference['friendOnly'] == 'N' || JWFriend::IsFriend( $idReceiver, $idSender ) ) {
					return $conference;
				}
			}
		}

		return array();;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Get Conference ById
	 */
	static public function GetDbRowById($idConference){
		$idConference = JWDB::CheckInt( $idConference );
		$sql = <<<_SQL_
SELECT * FROM Conference
	WHERE id = $idConference
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}

	/**
	 * Get User Conference Setting
	 */
	static public function GetDbRowFromUser($idUser){
		$idUser = JWDB::CheckInt( $idUser );
		$sql = <<<_SQL_
SELECT * FROM Conference
	WHERE
		idUser = $idUser
	LIMIT 1
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}

	/**
	 * Get Conference Setting By Number
	 */
	static public function GetDbRowFromNumber($number){
		if( null===$number )
			return array();

		$sql = <<<_SQL_
SELECT * FROM Conference
	WHERE
		`number` = '$number'
	LIMIT 1
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}
	
	/**
	 * Create User Conference Setting
	 */
	static public function Create( $idUser, $friendOnly='Y', $deviceAllow='sms,im,web', $number=null, $time=null){
		if(is_numeric( $time ) ) 
			$time = date('Y-m-d H:i:s', $time );
		$timeCreate = ($time==null) ? date('Y-m-d H:i:s') : $time;

		return JWDB::SaveTableRow('Conference', array(
					'idUser' =>  $idUser,
					'friendOnly' => $friendOnly,
					'deviceAllow' => $deviceAllow,
					'number' => $number,
					'timeCreate' => $timeCreate,
					));
	}

	/**
	 * Update User Conference Setting
	 */
	static public function Update( $idConference, $friendOnly='Y', $deviceAllow='sms,im,web', $number=null){
		$idConference = JWDB::CheckInt( $idConference );
		return JWDB::UpdateTableRow( 'Conference' , $idConference, array(
						'friendOnly' => $friendOnly,
						'deviceAllow' => $deviceAllow,
						'number' => $number,
					));
	}

	/**
	 * Update Row
	 */
	static public function UpdateRow( $idConference, $updatedRow = array() ){
		$idConference = JWDB::CheckInt( $idConference );
		return JWDB::UpdateTableRow( 'Conference' , $idConference, $updatedRow );
	}
}
?>
