<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Nudge Class
 */
class JWNudge {
	/**
	 * Instance of this singleton
	 *
	 * @var JWNudge
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWNudge
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
	 *	向 $idUSers 的设备上发送消息
	 *	@param	array of int	$idUsers
	 *	@param	string			$message
	 *	@type	string			$messageType	{'nudge'|'direct_messages'}
	 *	@return	
	 */
	static public function NudgeUserIds($idUsers, $message, $messageType='nudge')
	{
		$user_rows		= JWUser::GetUserDbRowsByIds			($idUsers);
		$device_rows	= JWDevice::GetDeviceRowsByUserIds	($idUsers);

		foreach ( $idUsers as $user_id )
		{
			$user_row	= $user_rows	[$user_id];
			$device_row	= @$device_rows	[$user_id];

			if ( empty($device_row) )
				continue;
			
			$device_send_via = $user_row['deviceSendVia'];

			if ( isset($device_row[$device_send_via]) )
				JWNudge::NudgeDevice( $device_row, $user_row['deviceSendVia'], $message, $messageType );
			else
				JWLog::Log(LOG_INFO, "JWNudge::NudgeUserIds User.deviceSendVia"
											."[$user_row[deviceSendVia]]"
 											."not exist in the device for user id[$user_id], skiped."
								);
		}
		return true;
	}


	/*
	 *	向一个 device 上发送消息
	 *	@param	array	$deviceRow	JWDevice::GetDeviceRowsByUserIds 的返回结构
	 *	@param	string	$type	{'sms'|'msn',...}
	 *	@param	string	$message
	 *	@param	string	$messageType	{'nudge'|'direct_messages'}
	 */
	static public function NudgeDevice( $deviceRow, $type, $message, $messageType , $nudgeFromIdUser = null)
	{
		// 对特定的 device ( sms / im） - 查看 Device.enabledFor:
		// enabledFor 可能有三个值: everything / nothing / direct_messages
		switch ( $deviceRow[$type]['enabledFor'] )
		{
			case 'direct_messages':
				if ( 'direct_messages'!=$messageType )
					break;
				// if equal, fall to everything: send it.

			case 'everything':

				// 检查设备是否已经验证通过
				$is_verified= $deviceRow[$type]['verified'];
				if ( !$is_verified )
				{
					JWLog::Log(LOG_INFO, "JWNudge::Nudge skip unverfied device for idUser"
										. '[' . $deviceRow[$type]['idUser'] . ']'
										. ' of device [' . $type
											. ':' .  $deviceRow[$type]['address']
								);
					break;
				}

				$address 	= $deviceRow[$type]['address'];

				JWRobot::SendMtRaw($address, $type, $message);

				break;

			case 'nothing':
				// fall to default
			default:
				JWLog::Log(LOG_INFO, "JWNudge::Nudge skip Device.enabledFor nothing for idUser"
									. '[' . $deviceRow[$type]['idUser'] . ']'
									. ' of device [' . $type
										. ':' .  $deviceRow[$type]['address']
							);
				break;
		}
	}

	/**
	 * 这个方法用来判断，发出去的Nudge消息，是否可以直接回复，目前仅仅 Sms 可以通过追加长号码的方法，使得用户，可以直接回复消息给特定的Nudge来源用户
	 */
	static public function GetReplyToIdUser( $type, $fromIdUser, $justSms=true) {
		if( 'sms' !== $type && true === $justSms ) 
			return null;

		$userInfo = JWUser::GetUserInfo( $fromIdUser );

		/* 
		 * 用户为会议账户 
		 */
		if( $userInfo['idConference'] && true === $justSms ) {
			return $userInfo['idConference'];
		}

		return null;
	}
}
?>
