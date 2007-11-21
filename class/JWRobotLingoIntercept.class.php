<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class
 */
class JWRobotLingoIntercept {
	
	/**
	 * Intercept follow command
	 */
	static public function Intercept_FollowOrLeave($robotMsg){
		
		$serverAddress = $robotMsg->GetServerAddress();
		$type = JWDevice::GetDeviceCategory( $robotMsg->GetType() );
		$mobileNo = $robotMsg->GetAddress();

		if( false == preg_match('/^(F|FOLLOW|L|LEAVE|DELETE|ON|OFF)\b/i', $robotMsg->GetBody() ) )
			return;

		if( $type == 'im' && preg_match('/^(F|FOLLOW|L|LEAVE|DELETE|ON|OFF)\b$/i', $robotMsg->GetBody() ) )
			return;

		$robotMsg->SetBody( self::BodyForStock( $robotMsg->GetBody() ) );

		if( in_array( $type, array('im', 'sms') ) )
			$robotMsg->SetBody( self::BodyForSmsFollow( $robotMsg->GetBody() ) );

		if( $type != 'sms' )
			return;

		$preAndId = JWFuncCode::FetchPreAndId( $serverAddress, $mobileNo );
		if( empty( $preAndId ) )
			return;
		
		$userInfo = null;
		switch( $preAndId['pre'] ){
			case JWFuncCode::PRE_STOCK_CATE: // Must > 100 < 999
			case JWFuncCode::PRE_CONF_CUSTOM: // Must be 0 - 99
				$conference = JWConference::GetDbRowFromNumber( $preAndId['id'] );
				if( empty($conference) )
					return;
				$userInfo = JWUser::GetUserInfo( $conference['idUser'] );
				if( empty($userInfo) )
					return;
			break;
			case JWFuncCode::PRE_CONF_IDUSER:
			case JWFuncCode::PRE_STOCK_CODE:
			case JWFuncCode::PRE_REG_INVITE:
				if( $preAndId['pre'] == JWFuncCode::PRE_STOCK_CODE ) {
					$userInfo = JWUser::GetUserInfo( 'gp'.$preAndId['id'] );
				}else{
					$userInfo = JWUser::GetUserInfo( $preAndId['id'] );
				}
				if( empty($userInfo) )
					return;
			break;
		}
		
		/*
		 * Intecept for sms follow
		 */
		$body = trim( $robotMsg->GetBody() ) . ' ' . $userInfo['nameScreen'];
		$robotMsg->SetBody( self::BodyForSmsFollow($body) );
	}

	static private function BodyForStock($body){
		return preg_replace( '/\b(\d{6})\b/', "gp\\1", $body );
	}

	static private function BodyForSmsFollow( $body ){
		return preg_replace( '/^(F|FOLLOW)\b/i', "Notice", $body );
	}
}
?>

