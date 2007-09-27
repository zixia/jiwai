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

		$robotMsg->SetBody( self::BodyForStock($body) );

		if( $type != 'sms' )
			return;

		$preAndId = JWFuncCode::FetchPreAndId( $serverAddress, $mobileNo );
		if( empty( $preAndId ) )
			return;

		switch( $preAndId['pre'] ){
			case JWFuncCode::PRE_STOCK_CATE:
			case JWFuncCode::PRE_CONF_CUSTOM:
				$conference = JWConference::GetDbRowById( $preAndId['id'] );
				if( empty($conference) )
					return;
				$userInfo = JWUser::GetUserInfo( $preAndId['id'] );
				if( empty($userInfo) )
					return;
				$body = trim( $body ) . ' ' . $userInfo['nameScreen'];
				$robotMsg->SetBody( self::BodyForStock($body) );
			break;
			case JWFuncCode::PRE_CONF_IDUSER:
			case JWFuncCode::PRE_STOCK_CODE:
				$userInfo = JWUser::GetUserInfo( $preAndId['id'] );
				if( empty($userInfo) )
					return;
				$body = trim( $body ) . ' ' . $userInfo['nameScreen'];
				$robotMsg->SetBody( self::BodyForStock($body) );
			break;
		}
	}

	static private function BodyForStock($body){
		return preg_replace( '/\b(\d{6})\b/', "gp\\1", $body );
	}
}
?>

