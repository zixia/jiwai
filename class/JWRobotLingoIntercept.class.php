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

	static public function Intercept_PreAndId($robot_msg)
	{
		$server_address = $robot_msg->GetHeader('serveraddress');
		$address = $robot_msg->GetAddress();
		$type = $robot_msg->GetType();
		$body = $robot_msg->GetBody();

		if ( 'sms' == $type )
		{
			$pre_and_id = JWFuncCode::FetchPreAndId( $server_address, $address );
			if ( false==empty($pre_and_id) )
			{
				switch( $pre_and_id['pre'] )
				{
					case JWFuncCode::PRE_MESSAGE_D:
						$user = JWUser::GetUserInfo($pre_and_id['id']);
						if ( $user )
						{
							$robot_msg->SetBody( "D $user[nameScreen] $body" );
						}
					break;
					case JWFuncCode::PRE_USER_RE:
						$user = JWUser::GetUserInfo($pre_and_id['id']);
						if ( $user )
						{
							$robot_msg->SetBody( "@$user[nameScreen] $body" );
						}
					break;
					case JWFuncCode::PRE_MYCAIFU_CODE:
						$id = $pre_and_id['id'];
						if ( false == preg_match('/^\d{6}$/', $body ) )
						{
							break;
						}
						if ( $id == '8' ) //stock
						{
							$robot_msg->SetBody( "ON $body" );
						}
						else if ( $id == '9' ) //fund
						{
							$robot_msg->SetBody( "ON J${body}" );
						}
					break;
					case JWFuncCode::PRE_VOTE_TP:
						$id = $pre_and_id['id'];
						if ( preg_match('/^\w+$/', $body ) )
						{
							$robot_msg->SetBody( "TP ${id} ${body}" );	
						}
					break;
					case JWFuncCode::PRE_REG_INVITE_13:
					case JWFuncCode::PRE_REG_INVITE_15:
						$mobileNo = $pre_and_id['pre'] . $pre_and_id['id'];
						$deviceInfo = JWDevice::GetDeviceDbRowByAddress($mobileNo, 'sms');
						$robotMsgtype = $robot_msg->GetHeader('msgtype');
						if (!empty($deviceInfo) && $deviceInfo['secret']==='') 
						{
							// Cast to PRE_USER_RE
							$user = JWUser::GetUserInfo($deviceInfo['idUser']);
							$robot_msg->SetIsInterceptable(false);
							$lingo_func = ( null==$robotMsgtype || 'NORMAL' == $robotMsgtype ) ?
							JWRobotLingoBase::GetLingoFunctionFromMsg($robot_msg) : null;

							$robot_msg->SetIsInterceptable(true);
							if ( empty($lingo_func) )
							{
								$robot_msg->SetBody( "@$user[nameScreen] $body" );
							}
						}
					break;
				}
			}
		}
	}

	static public function Intercept_TagDongZai($robot_msg)
	{
		$server_address = $robot_msg->GetHeader('serveraddress');
		$body = $robot_msg->GetBody();

		if ( '106693184001' == $server_address )
		{
			if( false == preg_match('/^(F|FOLLOW|L|LEAVE|DELETE|ON|OFF)\b/i', $robot_msg->GetBody() ) )
			{
				$body = '[冻灾] ' . $body;
				$robot_msg->SetBody( $body );
			}
		}
	}
	
	/**
	 * Intercept follow command
	 */
	static public function Intercept_FollowOrLeave($robotMsg){
		
		$serverAddress = $robotMsg->GetHeader('serveraddress');
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
		switch( $preAndId['pre'] )
		{
			case JWFuncCode::PRE_STOCK_CATE: // Must > 100 < 999
			case JWFuncCode::PRE_CONF_CUSTOM: // Must be 0 - 99
				$conference = JWConference::GetDbRowFromNumber( $preAndId['id'] );
				if( empty($conference) )
					return;
				$userInfo = JWUser::GetUserInfo( $conference['idUser'] );
			break;
			case JWFuncCode::PRE_CONF_IDUSER:
			case JWFuncCode::PRE_STOCK_CODE:
			case JWFuncCode::PRE_REG_INVITE:
				if( $preAndId['pre'] == JWFuncCode::PRE_STOCK_CODE ) {
					$userInfo = JWUser::GetUserInfo( $preAndId['id'], null, 'nameScreen');
				}else{
					$userInfo = JWUser::GetUserInfo( $preAndId['id'] );
				}
			break;
			case JWFuncCode::PRE_REG_INVITE_13:
			case JWFuncCode::PRE_REG_INVITE_15:
				$mobile_no = $preAndId['pre'] . $preAndId['id'];
				$device_row = JWDevice::GetDeviceDbRowByAddress($mobile_no, 'sms');
				if ( false==empty($device_row) && $device_row['idUser'] )
				{
					$userInfo = JWUser::GetUserInfo( $device_row['idUser'] );
				}	
			break;
		}

		if ( empty($userInfo) )
			return;
		
		/*
		 * Intecept for sms follow
		 */
		$body = trim( $robotMsg->GetBody() ) . ' ' . $userInfo['nameScreen'];
		$robotMsg->SetBody( self::BodyForSmsFollow($body) );
	}

	static private function BodyForStock($body){
		return preg_replace( '/\b(\d{6})\b/', "\\1", $body );
	}

	static private function BodyForSmsFollow( $body ){
		return preg_replace( '/^(F|FOLLOW)\b/i', "Notice", $body );
	}
}
?>
