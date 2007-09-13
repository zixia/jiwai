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
	 *	向 $idUsers 的设备上发送消息
	 *	@param	array of int	$idUsers
	 *	@param	string			$message
	 *	@type	string			$messageType	{'nudge'|'direct_messages'}
	 *	@return	
	 */
	static public function NudgeUserIds($idUsers, $message, $messageType='nudge', $source='bot')
	{
		if( empty($idUsers) )
			return true;

		$user_rows		= JWUser::GetUserDbRowsByIds			($idUsers);
		$device_rows	= JWDevice::GetDeviceRowsByUserIds	($idUsers);

		if( empty( $user_rows ) ) 
			return true;

		foreach ( $idUsers as $user_id )
		{
			$user_row	= @$user_rows	[$user_id];
			$device_row	= @$device_rows	[$user_id];

			if ( empty($device_row) )
				continue;
			
			$deviceSendVia = $user_row['deviceSendVia'];
			$availableSendVia = self::GetAvailableSendVia( $device_row, $deviceSendVia );

			if ( $availableSendVia && isset( $device_row[$availableSendVia] ) )
				JWNudge::NudgeDevice( $device_row, $availableSendVia, $message, $messageType, $source );
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
	static public function NudgeDevice( $deviceRow, $type, $message, $messageType , $source='bot' )
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
				if( JWDevice::IsAllowedNonRobotDevice($source) ) { // nudge,dm from wap|web
					$info = array(
						'message' => $message,
					);
					$queueType = JWNotifyQueue::T_NUDGE;
					$idUserTo = $deviceRow[$type]['idUser'];
					$ret = JWNotifyQueue::Create( null, $idUserTo, $queueType, $info );
				}else{
					if( is_array( $message ) ) {
						if( isset($message['type']) && $message['type'] == 'MMS' ) {
							if($type=='sms') {
								$idStatus = $message['idStatus'];
								$message = $message['sms'];
								$serverAddress = 
									JWFuncCode::GetMmsNotifyFunc($address,$idStatus );
								JWRobot::SendMtRaw($address,$type,$message,$serverAddress);
							}else{
								$message = $message['im'];
								JWRobot::SendMtRaw($address, $type, $message);
							}
						}
					}else{	
						JWRobot::SendMtRaw($address, $type, $message);
					}
				}
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
	 * 为用户选择发送通知的设备；
	 * 如果用户默认为 sms ，则未其检查在线其他im
	 * 如过默认为 web，不发送

	 */
	static public function GetAvailableSendVia( $deviceRow = array(), $deviceSendVia = 'web' ) {


		if( empty( $deviceRow ) )
			return null;
		
		$nudgeOrder = explode( ',' , $deviceSendVia );

		$shortcutArray = array();	
		foreach( $deviceRow as $type=>$row ){
			array_push( $shortcutArray, array( 'type' => $type, 'address' => $row['address'] ) );
		}

		$onlineArray = JWIMOnline::GetDbRowsByAddressTypes( $shortcutArray );

		foreach( $nudgeOrder as $device ){
			foreach( $onlineArray as $key=>$o ){
				if( 0 == strncasecmp( $key, $device, strlen($device) ) ){
					if( $o['onlineStatus'] !== 'OFFLINE' ) 
					       return $device;	
				}
			}
		}

		if( isset( $deviceRow['sms']) && in_array('sms', $nudgeOrder) ){
			return 'sms';
		}

		return null;
	}
}
/**
require_once( '../jiwai.inc.php' );
JWNudge::NudgeUserIds( array('89') , 'test', $messageType='nudge');
**/
?>
