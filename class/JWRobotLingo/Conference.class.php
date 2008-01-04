<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class
 */
class JWRobotLingo_Conference {
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

	/*
	 *
	 */
	static function	Lingo_Follow($robotMsg)
	{
		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$serverAddress  = $robotMsg->GetServerAddress();
		$type 		= $robotMsg->GetType();	

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($device_db_row) )
			$device_db_row = JWRobotLingo::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return null;

		$address_user_id = $device_db_row['idUser'];

		/**
	 	 * 解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = JWRobotLingoBase::ConvertCorner( $param_body );

		$followe = $robotMsg->GetIdUserConference();
		$followe_user_db_row = JWUser::GetUserInfo( $followe );

		if ( empty($followe_user_db_row) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_FOLLOW_NOUSER', array($followe) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}
	
		if ( $followe_user_db_row['idUser'] != $address_user_id  
				&& false == JWFollower::IsFollower($followe_user_db_row['idUser'], $address_user_id) ) {
			JWSns::CreateFollower( $followe_user_db_row['idUser'], $address_user_id, true );
		}
		
		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_FOLLOW_SUC', 
				array(
					$followe_user_db_row['nameFull'],
					$followe_user_db_row['nameScreen'],
				));
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/*
	 * Reg nameScreen nameFull
	 */
	static function Lingo_Reg($robotMsg){

		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();	
		$param_body 	= $robotMsg->GetBody();	
		
		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);
		if( false  == empty( $device_db_row ) )
			$user_info = JWUser::GetUserInfo( $device_db_row['idUser'] );

		$registered = true;
		if( empty( $device_db_row ) || empty($user_info) ){
			$registered = false;
		}

		$param_body = JWRobotLingoBase::ConvertCorner( $param_body );

		if ( preg_match('/^([[:alpha:]]+)\s+([\S]+)\s*([\S]*)$/',$param_body, $matches) ) {

			$nameScreen = $matches[2];
			
			if( false == JWUser::IsValidName( $nameScreen ) ){
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_INVALID_NAME', array( $nameScreen, ) );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply );

			}

			$nameFull = isset( $matches[3] ) ? $matches[3] : null;

			if( false == isset( $matches[3] ) ){
				if ( $registered == false )
					$nameFull = $nameScreen;
			}

			if( $registered == false ) {
				JWRobotLingo::CreateAccount( $robotMsg, $nameScreen, $nameFull );
				return null;
			}

			//only change nameFull
			if( $user_info['nameScreen'] == $nameScreen ) {
				if( $nameFull == null ) {
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SAME', array($nameScreen, ) );
					return JWRobotLogic::ReplyMsg($robotMsg, $reply );
			       	}else{
					$uRow = array( 'nameFull' => $nameFull );
					if( JWUser::Modify( $user_info['id'], $uRow) ){
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC_NICK', array($nameFull, ) );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
					}else{
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_500' );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
					}
				}
			}


			$email = in_array( $type, array('jabber','msn','gtalk','email') ) ? $address : null;
			$user_name = JWUser::GetPossibleName( $nameScreen, $email, $type );

			//if no nameFull , use user_name;
			if( $nameFull == null )
				$nameFull = $user_name;
			//end if

			if( empty($user_name) ) {
				if( $registered ) {
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_GM_HOT', array($user_name,));
				} else {
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_HOT', array($user_name,));
				}
				return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
			}else{

				$uRow = array('nameScreen' => $user_name );
				if ( null != $nameFull ) 
					$uRow['nameFull']  = $nameFull;

				if( JWUser::Modify( $user_info['id'], $uRow ) ){
					if( $nameFull == null ) {
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC_NICK', array($user_name, ) );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply);
					}else{
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC_ALL', array($nameFull, $user_name, ) );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
					}
				}else{
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_500' );
					return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
				}
			}

		}else{

			if( $registered ) {
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_HELP_GM' );
			}else{
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_HELP' );
			}
			return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
		}
	}
}
?>
