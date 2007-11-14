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
		$type = $robotMsg->GetType();
		$body = $robotMsg->GetBody();
		$mobileNo = $robotMsg->GetAddress();

		if( false == preg_match('/^(F|FOLLOW|L|LEAVE|DELETE)\b$/i', $body ) )
			return;

		if( $type != 'sms' )
			return;

		$preAndId = JWFuncCode::FetchPreAndId( $serverAddress, $mobileNo );
		if( empty( $preAndId ) )
			return;

		switch( $preAndId['pre'] ){
			case JWFuncCode::PRE_STOCK_CATE: // Must > 100 < 999
			case JWFuncCode::PRE_CONF_CUSTOM: // Must be 0 - 99
				$conference = JWConference::GetDbRowFromNumber( $preAndId['id'] );
				if( empty($conference) )
					return;
				$userInfo = JWUser::GetUserInfo( $conference['idUser'] );
				if( empty($userInfo) )
					return;
				$body = trim( $body ) . ' ' . $userInfo['nameScreen'];
				$robotMsg->SetBody( self::BodyForStockAndFollow($body) );
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
				$body = trim( $body ) . ' ' . $userInfo['nameScreen'];
				$robotMsg->SetBody( self::BodyForStockAndFollow($body) );
			break;
		}
	}

	static private function BodyForStockAndFollow($body){
		$body = preg_replace( '/^(F|FOLLOW)\b/i', "Notice", $body );
		return preg_replace( '/\b(\d{6})\b/', "gp\\1", $body );
	}
}
?>

