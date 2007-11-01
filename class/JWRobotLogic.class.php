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
		$serverAddress	= $robotMsg->GetServerAddress();
		$linkId		= $robotMsg->GetLinkId();

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
		$robotMsgtype = $robotMsg->getMsgtype();
		$lingo_func = ( null == $robotMsgtype || 'NORMAL' == $robotMsgtype ) ?
			JWRobotLingoBase::GetLingoFunctionFromMsg($robotMsg) : null;

		if ( !empty($lingo_func) )
		{
			$reply_robot_msg 	= call_user_func($lingo_func, $robotMsg);

		} else if ( JWDevice::IsExist($address, $type, false) || JWDevice::IsAllowedNonRobotDevice($type) )
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
				$reply_robot_msg = self::CreateAccount($robotMsg, true);
			else
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
		$serverAddress  = $robotMsg->GetServerAddress();
		$type		= $robotMsg->GetType();
		$body		= $robotMsg->GetBody();
		$msgtype	= $robotMsg->GetMsgtype();


		//Todo 2007-06-26
		$status_msgtype = array(null,'NORMAL','SIG');
		if( false == in_array( $msgtype, $status_msgtype ) ){
			return null; 
		}

		$isSignature = ( $msgtype=='SIG' ) ? 'Y' : 'N';
        
		$device_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		if( $isSignature == 'Y' && ( empty($device_row ) || false == empty($device_row['secret']) ) ) {
			return null;
		}

		if ( empty($device_row) )
		{	
			JWLog::Instance()->Log(LOG_NOTICE,"JWRobotLogic::ProcessMoStatus UNKNOWN IM: $type://$address");
			return JWRobotLogic::CreateAccount($robotMsg);
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

			$options = array();
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

				$index1 = strpos( $body, $qqString1 );
				if( $index1 ) $body = substr( $body, 0, $index1 );

				$index2 = strpos( $body, $qqString2 );
				if( $index2 ) $body = substr( $body, 0, $index2 );
			}

			$ret = JWSns::UpdateStatus($idUser, $body, $type, $time, $isSignature, $serverAddress, $options );
			if( $ret ) {
				$nameFull = JWUser::GetUserInfo( $device_row['idUser'], 'nameFull' );
				$reply = JWRobotLingoReply::GetReplyString($robotMsg,'REPLY_UPDATESTATUS',array($nameFull,));
				if( $reply ) {
					return self::ReplyMsg( $robotMsg, $reply );
				}else {
					return null;
				}
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
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_VERIFY_FAIL', array($secret) );
		}

		return self::ReplyMsg($robotMsg, $reply);
	}

	/**
	 * 一些会议系统需要强制注册；
	 */
	static public function ForceCreateAccount($robotMsg ) {

		$idUserConference = $robotMsg->GetIdUserConference();	
		$address = $robotMsg->GetAddress();
		$type = $robotMsg->GetType();

		switch( $idUserConference ) {
			case 99:
			{
				$uaddress = 'u'.preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]XXXX$m[3]";'), $address);
				$nameScreen = JWUser::GetPossibleName( $uaddress, $address, $type );
				$nameFull = '午夜过客';
				$bio = $address;
				$bio = preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]****$m[3]";'), $address);

				if( !$nameScreen ){
					return false;
				}
				$new_user_row = array(
						'nameScreen'	=> $nameScreen,
						'nameFull'	=> $nameFull,
						'bio'		=> $bio,
						'pass'		=> JWDevice::GenSecret(16),
						'isWebUser'	=> 'N', 
						'noticeAutoNudge' => 'N', // not nudge
						'ip' => JWRequest::GetIpRegister($type),
				);

				if ( in_array( $type, array('msn','gtalk','newsmth', 'jabbar') ) ){
					$new_user_row['email'] = $address;
				}
		
				// 增加了 isWebUser 标志，允许用户去 Web 上注册用户
				$new_user_id =  JWSns::CreateUser($new_user_row);
				if( $new_user_id ) {
					JWSns::CreateDevice($new_user_id, $address, $type, true);
				}else{
					return false;
				}
				return true;
			}
			break;
			case 28006:
			{
				$uaddress = 'u'.preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]XXXX$m[3]";'), $address);
				$nameScreen = JWUser::GetPossibleName( $uaddress, $address, $type );
				$nameFull = '午夜过客';
				$bio = $address;
				$bio = preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]****$m[3]";'), $address);

				if( !$nameScreen ){
					return false;
				}
				$new_user_row = array(
						'nameScreen'	=> $nameScreen,
						'nameFull'	=> $nameFull,
						'bio'		=> $bio,
						'pass'		=> JWDevice::GenSecret(16),
						'isWebUser'	=> 'N', 
						'noticeAutoNudge' => 'N',   //Not nudge
						'ip' => JWRequest::GetIpRegister($type),
				);

				if ( in_array( $type, array('msn','gtalk','newsmth', 'jabbar') ) ){
					$new_user_row['email'] = $address;
				}
		
				// 增加了 isWebUser 标志，允许用户去 Web 上注册用户
				$new_user_id =  JWSns::CreateUser($new_user_row);
				if( $new_user_id ) {
					JWSns::CreateDevice($new_user_id, $address, $type, true);
				}else{
					return false;
				}
				return true;
				return true;
			}
			break;
		}
		return null;
	}

	/*
	 *	如果在 Device 表中找不到这个设备，并且发送的也不是机器人命令的话，到这里来注册
	 */
	static public function CreateAccount($robotMsg, $toRegister=false, $nameScreen= null, $nameFull=null )
	{
		$address = $robotMsg->GetAddress();
		$type	 = $robotMsg->GetType();
		$body	 = $robotMsg->GetBody();
		$serverAddress = $robotMsg->GetServerAddress();
		$linkId = $robotMsg->GetLinkId();
		$idUserConference = $robotMsg->GetIdUserConference();

		$forceCreate = self::ForceCreateAccount( $robotMsg );

		if( false === $forceCreate ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_MSG' );
			return self::ReplyMsg( $robotMsg, $reply );
		}else if( true === $forceCreate ){
			self::ProcessMo( $robotMsg );
			$device_row = JWDevice::GetDeviceDbRowByAddress($address,$type);
			$nameFull = JWUser::GetUserInfo( $device_row['idUser'], 'nameFull' );
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC', array($nameFull,) );
			return self::ReplyMsg( $robotMsg, $reply );
		}

		$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array('address'=>$address,'type'=>$type) ); 

		$last_robot_msg_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWRobotLogic', 'CreateAccount'), $address );
		$memcache = JWMemcache::Instance();
		$beforeRegister = $memcache->Get( $last_robot_msg_key );

		// Not be invited and not register by lingo REG and is the first message
		/**
		 * Remember the last message before register
		 */
		if ( empty($invitation_id) && null == $nameScreen && empty($beforeRegister) )
		{
			$memcache = JWMemcache::Instance();
			$memcache->Set( $last_robot_msg_key, array(
						'body' => $body,
						'address' => $address,
						'type' => $type,
						'serverAddress' => $serverAddress,
						'linkId' => $linkId,
						), 0, 3600 );
			/*
			 * register msg
			 */
			$msgRegister = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_NOREG_TIPS');
			$parseInfo = JWFuncCode::FetchConference( $robotMsg->GetServerAddress(), $address );
			if( false == empty( $parseInfo ) && $parseInfo['conference']['msgRegister'] ) {
				$msgRegister = $parseInfo['conference']['msgRegister'];
			}

			return JWRobotLogic::ReplyMsg($robotMsg, $msgRegister);
		}

		/**
		 * 用户被邀请过（通过设备查找到邀请） || 主动注册 
		 */
		$param_body = $robotMsg->GetBody();

		if ( $nameScreen == null ) {
			$user_name	= JWUser::GetPossibleName($param_body, $robotMsg->GetAddress(), $robotMsg->GetType());
			$user_nameFull 	= $user_name;
		}else{
			$user_name	= $nameScreen;
			$user_nameFull 	= $nameFull;
		}

//die("[$user_name]");
		if ( empty($user_name) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_REG_HOT', array($param_body) );
			return self::ReplyMsg($robotMsg, $reply);
		}

	
		$new_user_row = array( 	'nameScreen'	=> $user_name,
					'nameFull'	=> $user_nameFull,
					'pass'		=> JWDevice::GenSecret(16),
					'isWebUser'	=> 'N', // 很重要：设备注册，要设置标志，方便未来Web上设置密码
					'ip' => JWRequest::GetIpRegister($type),
				);

		if ( in_array( $type, array('msn','gtalk','newsmth', 'jabbar') ) )
			$new_user_row['email'] = $address;
		
	
		// 增加了 isWebUser 标志，允许用户去 Web 上注册用户
		$new_user_id =  JWSns::CreateUser($new_user_row);

		if ( $new_user_id )
		{
			//Create User Success
			if ( ! JWSns::CreateDevice($new_user_id, $address, $type, true) )
				JWLog::LogFuncName(LOG_CRIT, "JWDevice::Create($new_user_id,$address,$type,true) failed.");

			// 互相加为好友，标识邀请状态
			if( $invitation_id ) {
				JWSns::FinishInvitation($new_user_id, $invitation_id);
			}

			/*
			 * 检查用户注册前的更新，将其发出
			 */
			$memcache = JWMemcache::Instance();
			$beforeRegister = $memcache->Get( $last_robot_msg_key );

			if( !empty( $beforeRegister ) && is_array($beforeRegister) )
			{
				$memcache->Del( $last_robot_msg_key );

				// 7/24/07 zixia: 如果之前的消息有回复，则返回给用户命令操作的返回，而不是注册成功提示。
				$beforeRegisterMsg = new JWRobotMsg();
				$beforeRegisterMsg->Set( $beforeRegister['address']
						, $beforeRegister['type']
						, $beforeRegister['body']
						, $beforeRegister['serverAddress']
						, $beforeRegister['linkId']
						);
				$reply_msg = self::ProcessMo($beforeRegisterMsg);

				if ( ! empty($reply_msg) )
				{
					$reply_msg->SetBody( $reply_msg->GetBody() );
					return $reply_msg;
				}
			}
			
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_REG_REPLY_SUC', array($new_user_row['nameScreen']) );
	       	}
		else
		{
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_REG_500' );
		}

		return self::ReplyMsg($robotMsg, $reply);
	}


	static public function ReplyMsg($robotMsg, $message)
	{
		$robot_reply_msg = new JWRobotMsg();

		$robot_reply_msg->Set( $robotMsg->GetAddress()
					, $robotMsg->GetType()
					, $message
					, $robotMsg->GetServerAddress()
					, $robotMsg->GetLinkId()
				);

		return $robot_reply_msg;
	}

}
?>
