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

	static public function NudgeToUsers($idUsers, $message=null, $messageType='nudge', $source='bot', $options=array() ){
		if( empty( $idUsers ) )
			return true;

		settype( $idUsers, 'array' );
		$idUsers = array_unique( $idUsers );

		if( false == in_array( $source, array('bot','msn','gtalk','sms','qq','skype') ) ) {
			if( JWDevice::IsAllowedNonRobotDevice($source) ) { 
				$metaInfo = array(
					'idUsers' => $idUsers,
					'message' => $message,
					'messageType' => $messageType,
					'source' => 'bot',
					'options' => $options,
				);
				return JWNotifyQueue::Create( null, null, JWNotifyQueue::T_WEBNUDGE, $metaInfo );
			}
			return true;
		}

		$idConference = isset( $options['idConference'] ) ? intval( $options['idConference'] ) : null;
		$idStatus = isset( $options['idStatus'] ) ? intval( $options['idStatus'] ) : null;

		$status = ( $idStatus == null ) ? null : JWStatus::GetDbRowById( $idStatus );
		$conference = ( $idConference == null ) ? null : JWConference::GetDbRowById( $idConference );
		$user = ( $conference == null ) ? null : JWUser::GetUserInfo( $conference['idUser'] );
		$user = ( $user == null ) ? ( $status == null ? null : JWUser::GetUserInfo($status['idUser']) ) : $user;

		$nudgeOptions = array(
			'conference' => $conference,
			'status' => $status,
			'user' => $user,
		);

		foreach( $idUsers as $idUser ){
			$userTo = JWUser::GetUserInfo( $idUser );
			if( empty( $userTo ) )
				continue;

			$deviceRows= JWDevice::GetDeviceRowByUserId( $idUser );
			if( empty( $deviceRows ) )
				continue;

			$deviceSendVia = $userTo['deviceSendVia'];
			$availableSendVia = self::GetAvailableSendVia_Temp( $deviceRows, $deviceSendVia );

			if( null == $availableSendVia )
				continue;

			if( $messageType == 'direct_messages' ) {
				$idMessage = $message['idMessage'];
				JWMessage::SetMessageStatus( $idUser, JWMessage::INBOX, JWMessage::MESSAGE_HAVEREAD );
				$message = $message['message'];
			}

			$deviceRow = $deviceRows[ $availableSendVia ];
			JWNudge::NudgeToUserDevice( $deviceRow, $message, $messageType, $nudgeOptions );
		}
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

		$user_rows	= JWUser::GetUserDbRowsByIds			($idUsers);
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
			$availableSendVia = self::GetAvailableSendVia_Temp( $device_row, $deviceSendVia );

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


	static public function NudgeToUserDevice( $deviceRow, $message, $messageType, &$options=array() ) {
		
		switch( $deviceRow['enabledFor'] ){
			case 'direct_messages':
				if( 'direct_messages' != $messageType )
					break;
			case 'everything':
				// 检查设备是否已经验证通过
				$isVerified= $deviceRow['verified'];
				if ( false == $isVerified )
				{
					JWLog::Log(LOG_INFO, "JWNudge::Nudge skip unverfied device for idUser"
										. '[' . $deviceRow['idUser'] . ']'
										. ' of device [' . $deviceRow['type'] 
										. ':' .  $deviceRow['address']
					);
					break;
				}
				
				//fetch from nudge options
				$user = $options['user'];
				$conference = $options['conference'];
				$status = $options['status'];
				$isMms = ( $status == null ) ? false : ($status['isMms']=='Y');
				
				//fetch from deviceRow
				$type = $deviceRow['type'];
				$address = $deviceRow['address'];

				if( is_array( $message ) ){
					if( $type == 'sms' ){
						$message = $message[ 'sms' ];
					}else{
						$message = $message[ 'im' ];
					}
				}

				$serverAddress = null;
				if( $type=='sms' && $serverAddress==null && $isMms ) {
					$serverAddress = JWFuncCode::GetMmsNotifyFunc($address, $status['id'] );
				}
				if( $type=='sms' && $serverAddress==null ) {
					$serverAddress = JWNotify::GetServerAddress( $address, $conference, $user );
				}

				if( $serverAddress == null ) {
					JWRobot::SendMtRaw($address, $type, $message);
				}else{
					JWRobot::SendMtRaw($address, $type, $message, $serverAddress);
				}
			break;
			case 'nothing':
			break;
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
						if( isset($message['type']) ){
							if( $message['type'] == 'MMS' ) {
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
							}else if( $message['type'] == 'CONFERENCE' ) {
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
	 * 选取在线的设备发送，如果选定msn，且不在线，那么不发送； 
	 */
	static public function GetAvailableSendVia_Temp( $deviceRows = array(), $deviceSendVia = 'web' ) {

		if( empty( $deviceRows ) || $deviceSendVia == 'web' )
			return null;

		if( false == isset( $deviceRows[ $deviceSendVia ] ) )
			return null;

		/*
		$online = JWIMOnline::GetDbRowByAddressType( $deviceRows[ $deviceSendVia ]['address'] , $deviceSendVia );
		if( false == empty( $online ) && $online['onlineStatus'] == 'OFFLINE' )
			return null;
		*/
		return $deviceSendVia;
	}
	
	/**
	 * 为用户选择发送通知的设备；
	 * 如果用户默认为 sms ，则未其检查在线其他im
	 * 如过默认为 web，不发送  | default MSN/GTALK/SKYPE/QQ/SMS/WEB
	 */
	static public function GetAvailableSendVia( $deviceRow = array(), $deviceSendVia = 'web' ) {
		
		//如果没有设备，或用户接受设备为web，那么不需要nudge
		if( empty( $deviceRow ) || $deviceSendVia == 'web' )
			return null;

		return $deviceSendVia;
		
		$originOrder = $deviceSendVia;
		//$nudgeOrder = explode( ',' , $deviceSendVia );
		$nudgeOrder = array( 'msn', 'gtalk', 'skype', 'qq', 'sms' );

		$shortcutArray = array();	
		foreach( $deviceRow as $type=>$row ){
			// 用户选了不用此设备接受更新，那么算了吧；
			if( $row['enabledFor'] == 'nothing' ) {
				$nudgeOrder = array_diff( $nudgeOrder, array( $type ) );
				continue;
			}
			array_push( $shortcutArray, array( 'type' => $type, 'address' => $row['address'] ) );
		}

		$onlineArray = JWIMOnline::GetDbRowsByAddressTypes( $shortcutArray );

		$onlineIms = array();
		foreach( $nudgeOrder as $device ){
			foreach( $onlineArray as $key=>$o ){
				if( 0 == strncasecmp( $key, $device, strlen($device) ) ){
					if( $o['onlineStatus'] !== 'OFFLINE' ) 
						array_push( $onlineIms, $device );
				}
			}
		}
		
		//如果有在线的IM 设备，选择发送
		if( in_array( $originOrder, $onlineIms ) ) {
			return $originOrder;
		} else if( false == empty( $onlineIms ) ) {
			return $onlineIms[0];
		}
		
		//如果选定了QQ，那么即使不在线，也发送，我们无法判定QQ在线
		if( isset( $deviceRow['qq']) && $originOrder == 'qq' ){
			return 'qq';
		}
		
		//如果到了这里，且绑定了手机，那么发短信吧；
		if( isset( $deviceRow['sms']) ){
			return 'sms';
		}

		return null;
	}
}
?>
