<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class
 */
class JWRobotLingo_Add {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingo
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWRobotLingo
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

	/**
	 * DM Deal Function.
	 */
	static function Lingo_DM($robotMsg) 
	{
		$type = $robotMsg->GetType();
		$address = $robotMsg->GetAddress();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		if( $type != 'sms' ) {
			$reply = JWRobotLingo_AddReply::GetReplyString( $robotMsg, 'REPLY_MMS_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$mmsId = JWFuncCode::FetchMmsIdStatus($serverAddress, $address );
		if( null == $mmsId ) {
			$reply = JWRobotLingo_AddReply::GetReplyString( $robotMsg, 'REPLY_MMS_ILL' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$idUser = JWDevice::IsAllowedNonRobotDevice( $type ) ? $address : $device_db_row['idUser'];
		$userReceiver = JWUser::GetUserInfo( $idUser );

		
		$mmsRow = JWStatus::GetDbRowById( $mmsId );
		if( empty($mmsRow) || $mmsRow['isMms']=='N' || $mmsRow['idPicture']==null || $mmsRow['idUser']==null ){
			$reply = JWRobotLingo_AddReply::GetReplyString( $robotMsg, 'REPLY_MMS_NOMMS', array($userReceiver['nameFull'] ) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$userSender = JWUser::GetUserInfo( $mmsRow['idUser'] );

		if( $mmsRow['isProtected']=='Y' && false == JWFollower::IsFollowing( $userSender['id'], $userReceiver['id'] ) ){
			$reply = JWRobotLingo_AddReply::GetReplyString( $robotMsg, 'REPLY_MMS_NOPERM', array($userReceiver['nameFull'], $userSender['nameFull'] ) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}	
		
		$sendMMS = ( $type=='sms' ) ? true : false;

		if( $sendMMS == true ) {
			//Send MMS To Queue [Async]
			JWMmsQueue::Create( $address, $mmsRow['id'] );
		}

		if( isset($reply) && $reply ) {
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		return null;
	}

	/**
	 *
	 */
	static function Lingo_F($robotMsg)
	{
		$serverAddress = $robotMsg->GetServerAddress();
		$mobileNo = $robotMsg->GetAddress();
		$type = $robotMsg->GetType();

		$idUser = JWFuncCode::FetchRegIdUser($serverAddress, $mobileNo);
		$userInfo = JWUser::GetUserInfo( $idUser );

		if ( false == JWDevice::IsExist( $mobileNo, $type ) ){

			$body = $robotMsg->GetBody();
			$body = JWRobotLingoBase::ConvertCorner( $body );

			if ( preg_match('/^F\s+(\S+)\s*(\S*)\s*$/i',$body,$matches) ) {
				$uaddress = $matches[1];
				$nameFull = @$matches[2];
			}else{
				$uaddress = 'u'.preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]XXXX$m[3]";'), $mobileNo);
				$nameFull = $uaddress;
			}

			$nameScreen = JWUser::GetPossibleName( $uaddress, $mobileNo, $type );

			JWRobotLogic::CreateAccount($robotMsg, true, $nameScreen, $nameScreen, $nameFull);
		}

		$device_db_row = JWDevice::GetDeviceDbRowByAddress( $mobileNo, $type );

		if( empty( $device_db_row ) ){
			$reply = JWRobotLingo_AddReply::GetReplyString($robotMsg, 'REPLY_F_HOT', array($mobileNo) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		//邀请自己，无意义
		if( $idUser == $device_db_row['idUser'] ) {
			$reply = JWRobotLingo_AddReply::GetReplyString($robotMsg, 'REPLY_F_SELF', 
					array(
						$userInfo['nameFull'], 
						$mobileNo, 
					));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		//被邀请人成功注册了用户；
		$newUserInfo = JWUser::GetUserInfo( $device_db_row['idUser'] );
		if( $idUser ) {
			JWSns::CreateFriends($idUser, array($device_db_row['idUser']), true );
			$userInfo = JWUser::GetUserInfo( $idUser );	
			$reply = JWRobotLingo_AddReply::GetReplyString($robotMsg, 'REPLY_F_SUC_Y_FOLLOW', 
					array(
						$newUserInfo['nameScreen'],
					       	$userInfo['nameFull'], 
						$userInfo['nameScreen'],
					));
		}else{
			$reply = JWRobotLingo_AddReply::GetReplyString($robotMsg, 'REPLY_F_SUC_N_FOLLOW', 
					array(
						$newUserInfo['nameScreen'],
					));
		}
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}
}
?>
