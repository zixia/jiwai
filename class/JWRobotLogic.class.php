<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Logic Class
 */
class JWRobotLogic {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLogic
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWRobotLogic
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
	 * @return 
	 * 		JWRobotMsg				- reply one msg
	 * 		TODO array of JWRobotMsg		- reply many msg
	 * 		null					- no need to reply
	 * 		false					- in case of error
	 */
	static public function ProcessMo( $robotMsg=null )
	{
		if (empty ($robotMsg))
		{
			JWLog::LogFuncName(LOG_CRIT, 'received a empty msg' );
			return null;
		}
		
		if ( ! $robotMsg->IsValid() )
		{
			JWLog::LogFuncName(LOG_CRIT, 'received a invalid msg' );
			return false;
		}

		$address	= $robotMsg->GetAddress();
		$type		= $robotMsg->GetType();
		$body 		= $robotMsg->GetBody();

		$serverAddress	= $robotMsg->GetHeader('ServerAddress');
		$linkId		= $robotMsg->GetHeader('linkid');

		// echo
		if( false == JWDevice::IsAllowedNonRobotDevice($type) )
			printf("%-35s: %s\n", "MO($type://$address)", $body);

		/*
		 *	一个 MO 消息有如下几种状态：
		 	仅当msgtype为空时
				if ( 是机器人指令 )
				else if (是用户绑定的设备)
				else //不是用户绑定的设备

				1、用户从来没有用过，发送给特服号码一条短信
					邀请表中无记录
					或者是 accept
				2、用户从来没有用过，发送给特服号码一条短信后，得到输入用户名的提示，又发回来了用户名
					邀请表中有记录
				3、用户是被邀请来的，发送了accept/deny
		 *
		 *
		 *
		 */
		$robotMsgtype = $robotMsg->GetHeader('msgtype');
		$lingo_func = ( null == $robotMsgtype || 'NORMAL' == $robotMsgtype ) ?
			JWRobotLingoBase::GetLingoFunctionFromMsg($robotMsg) : null;

		if ( !empty($lingo_func) )
		{
			$reply_robot_msg 	= call_user_func($lingo_func, $robotMsg);

		} else if ( JWDevice::IsExist($address, $type, false) 
				|| JWDevice::IsAllowedNonRobotDevice($type) 
				|| JWRobotLingo::CreateAccount($robotMsg)
		)
		{
			// 设备已经设置，(false 代表包含未激活的设备)
			// 		1、user JiWai status
			//		2、verify code

			$reply_robot_msg = self::ProcessMoStatus($robotMsg);
		}
		else 
		{
			// 非注册用户（在Device表中没有的设备）
			if( $robotMsgtype == null || $robotMsgtype == 'NORMAL' )
				JWRobotLingo::CreateAccount($robotMsg);
			$reply_robot_msg = null; 
		}

		if ( empty($reply_robot_msg) ) {
			$msg = "MT: none\n";
		} else {
			$msg = sprintf("%-35s: %s\n", "MT(" . $reply_robot_msg->GetType()
						. "://" . $reply_robot_msg->GetAddress()
						. ")"
						, $reply_robot_msg->GetBody() );
		}

		if( false == JWDevice::IsAllowedNonRobotDevice($type) )
			echo $msg;

		return $reply_robot_msg;
	}


	static function ProcessMoStatus($robotMsg)
	{
		$address	= $robotMsg->GetAddress();
		$type		= $robotMsg->GetType();
		$body		= $robotMsg->GetBody();

		$serverAddress  = $robotMsg->GetHeader('ServerAddress');
		$msgtype	= $robotMsg->GetHeader('Msgtype');


		//Todo 2007-06-26
		$status_msgtype = array(null,'NORMAL','SIG');
		if( false == in_array( $msgtype, $status_msgtype ) ){
			return null; 
		}
		
		//ADD 2008-04-11
		$statusType = 'NONE';
		'SIG'==$msgtype && $statusType='SIG';
		$options=array(
			'statusType' => $statusType,
		);

		$device_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		if( 'SIG'==$statusType && ( empty($device_row ) || false == empty($device_row['secret']) ) ) {
			return null;
		}

		if ( empty($device_row) )
		{	
			JWLog::Instance()->Log(LOG_NOTICE,"JWRobotLogic::ProcessMoStatus UNKNOWN IM: $type://$address");
			JWRobotLingo::CreateAccount($robotMsg);
			return null;
		}
		else if ( false == empty($device_row['secret']) )
		{	
			JWLog::Instance()->Log(LOG_INFO,"VERIFY:\t$device_row[idUser] $device_row[secret]");
			return self::ProcessMoVerifyDevice($robotMsg);
		}
		else
		{	
			$time = $robotMsg->GetCreateTime();
			JWLog::Instance()->Log(LOG_INFO,"UPDATE:\t$device_row[idUser] @$type: $body $time");
			$idUser = $device_row['idUser'];

			if( $type == 'sms' ) {
				$parseInfo = JWFuncCode::FetchConference($serverAddress, $address);
				if( false == empty( $parseInfo ) ){
					$options[ 'idConference'] = $parseInfo['conference']['id'] ;
				}
			}else if( $type == 'qq' ) { 
				/**
				 * 以下代码为了把其他客户端QQ的附加话语去除；
				 */
				$qqString1 = '(本消息发自腾讯官方';
				$qqString2 = '（您的好友正在使用手机QQ';
				$qqString3 = '（ 您的好友正在使用手机QQ';

				$index1 = strpos( $body, $qqString1 );
				if( $index1 ) $body = substr( $body, 0, $index1 );

				$index2 = strpos( $body, $qqString2 );
				if( $index2 ) $body = substr( $body, 0, $index2 );

				$index3 = strpos( $body, $qqString3 );
				if( $index3 ) $body = substr( $body, 0, $index3 );
			}

			$ret = JWSns::UpdateStatus($idUser, $body, $type, $time, $serverAddress, $options );
			if( $ret > 0 ) 
			{
				$name_screen = JWUser::GetUserInfo( $device_row['idUser'], 'nameScreen' );

				$status_row = JWDB_Cache_Status::GetDbRowById( $ret );
				if ( empty($status_row) || null==$status_row['idConference'] )
					return null;

				$conference_id = $status_row['idConference'];
				$device_type = JWDevice::GetDeviceCategory( $type );
				$reply_status_constant = 'REPLY_UPDATESTATUS';
				if ('im'==$device_type)
					$reply_status_constant = 'REPLY_UPDATESTATUS_IM';

				$reply = JWRobotLingoReply::GetReplyString(null, $reply_status_constant, 
					array( $name_screen, $ret, strtoupper($type)),
					array('conference_id'=>$conference_id) );

				if( $reply ) {
					return self::ReplyMsg( $robotMsg, $reply );
				}else {
					return null;
				}
			}
			else if ( JWStatus::STATUS_FILTERED == $ret ) //Filtered
			{
				$reply = JWRobotLingoReply::GetReplyString(null, 'REPLY_UPDATESTATUS_FILTERED');

				if( $reply )
				{
					/**
				 	 * Send a D message plus from 叽歪小弟
					 */
					$xiaodi = JWUser::GetUserInfo('叽歪小弟');
					JWSns::CreateMessage( $xiaodi['id'], $idUser, $reply, 'web', $options=array('delete'=>true, 'notice'=>true,) );	
					return self::ReplyMsg( $robotMsg, $reply );
				}
				return null;
			}
			else if ( JWStatus::STATUS_REPEATED == $ret ) // repeated
			{
				$reply = JWRobotLingoReply::GetReplyString(null, 'REPLY_UPDATESTATUS_REPEATED');
				return self::ReplyMsg( $robotMsg, $reply );
			}
			else if ( JWStatus::STATUS_BLOCKED == $ret ) // repeated
			{
				$reply = JWRobotLingoReply::GetReplyString(null, 'REPLY_UPDATESTATUS_BLOCKED');
				return self::ReplyMsg( $robotMsg, $reply );
			}
			else
			{
				return false;
			}
		}
	}


	/**
	 * @return: null or RobotMsg to reply.
	 */
	static function ProcessMoVerifyDevice($robotMsg)
	{
		if ( ! preg_match('/^\s*(\S+)/',$robotMsg->GetBody(),$matches) )
		{
			return JWRobotLingo::Lingo_Help($robotMsg);
		}

		$secret = $matches[1];

		$type = $robotMsg->GetType();

		$user_id = JWSns::VerifyDevice($robotMsg->GetAddress()
										, $type
										, $secret
										);

		if ( $user_id ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_VERIFY_SUC');
		} else {
			$device = 'sms'==$type ? 'sms' : 'im';
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_VERIFY_FAIL', array($secret, $device) );
		}

		return self::ReplyMsg($robotMsg, $reply);
	}

	static public function ReplyMsg($robotMsg, $message)
	{
		$robot_reply_msg = new JWRobotMsg();

		$robot_reply_msg->Set( $robotMsg->GetAddress() , $robotMsg->GetType() , $message );
		$robot_reply_msg->SetHeaders( $robotMsg->GetHeaders() );

		return $robot_reply_msg;
	}
}
?>
