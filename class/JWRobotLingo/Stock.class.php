<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class For Stock
 */
class JWRobotLingo_Stock {

	/*
	 * ZX|ZC nameScreen nameFull
	 */
	static function Lingo_ZX($robotMsg){

		$address = $robotMsg->GetAddress();
		$type 	= $robotMsg->GetType();	
		$body 	= $robotMsg->GetBody();	
		$serverAddress = $robotMsg->GetServerAddress();
		
		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);
		if( false  == empty( $device_db_row ) )
			$user_info = JWUser::GetUserInfo( $device_db_row['idUser'] );

		$registered = true;
		if( empty( $device_db_row ) || empty($user_info) ){
			$registered = false;
		}

		if( $registered == true ) {
			$reply = JWRobotLingo_StockReply::GetReplyString( $robotMsg, 'REPLY_ZX_YET', array(
							JWNotify::GetPrettySender( $user_info ),
						) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( preg_match('/^([[:alpha:]]+)\s+([\S]+)\s*([\S]*)$/',$body, $matches) ) {

			$nameScreen = $matches[2];
			$nameFull = isset( $matches[3] ) ? $matches[3] : $nameScreen;

			if( false == JWUser::IsValidName( $nameScreen ) ){
				$nameScreen = JWUser::GetPossibleName( $body, $address, $type );
			}

			if(null == $nameScreen ) {
				$reply = JWRobotLingo_StockReply::GetReplyString( $robotMsg, 'REPLY_ZX_HOT' );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}

			$device_db_row = JWRobotLingo::CreateAccount( $robotMsg, $nameScreen, $nameFull );

			if ( empty( $device_db_row ) ){
				$reply = JWRobotLingo_StockReply::GetReplyString( $robotMsg, 'REPLY_ZX_HOT' );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}

			$userInfo = JWUser::GetUserInfo( $device_db_row['idUser'] );
			$parseInfo = JWFuncCode::FetchConference( $serverAddress, $address );
			if( false == empty( $parseInfo ) ){
				$follower_ids = JWFollowRecursion::GetSuperior( $parseInfo['user']['id'] , 3 );

				foreach( $follower_ids as $id ) {
					JWSns::CreateFollowers($id, array($userInfo['id']), false);
				}

				$reply = JWRobotLingo_StockReply::GetReplyString( $robotMsg, 'REPLY_ZX_SUC_F', array(
							JWNotify::GetPrettySender( $userInfo ),
							JWNotify::GetPrettySender( $parseInfo['user'] ),
						) );
			}else{
				$reply = JWRobotLingo_StockReply::GetReplyString( $robotMsg, 'REPLY_ZX_SUC', array(
								JWNotify::GetPrettySender( $user_info ),
							) );
			}

			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$reply = JWRobotLingo_StockReply::GetReplyString( $robotMsg, 'REPLY_ZX_HELP' );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}
}
?>
