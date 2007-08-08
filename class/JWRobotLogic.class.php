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
		
//var_dump($robotMsg);
		if ( ! $robotMsg->IsValid() )
		{
			JWLog::LogFuncName(LOG_CRIT, 'received a invalid msg' );
			return false;
		}

		$address	= $robotMsg->GetAddress();
		$type		= $robotMsg->GetType();
		$body 		= $robotMsg->GetBody();
		$serverAddress	= $robotMsg->GetServerAddress();

		// echo
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
			JWRobotLingo::GetLingoFunctionFromMsg($robotMsg) : null;

		if ( !empty($lingo_func) )
		{
			$reply_robot_msg 	= call_user_func($lingo_func, $robotMsg);

		}else if( $robotMsgtype == 'ONOROFF' ) {  // FOR user online/offline msg

			return null;

		} else if ( JWDevice::IsExist($address, $type, false) )
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
		if( ! in_array( $msgtype, $status_msgtype ) ){
			//to do;
			return null; 
		}
		$isSignature = ( $msgtype=='SIG' ) ? 'Y' : 'N';

		$device_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($device_row) )
		{	
			// user not registed
			JWLog::Instance()->Log(LOG_NOTICE,"JWRobotLogic::ProcessMoStatus UNKNOWN IM: $type://$address");
			return JWRobotLogic::CreateAccount($robotMsg);
		}
		else if ( ! empty($device_row['secret']) )
		{	
			// device not verified
			JWLog::Instance()->Log(LOG_INFO,"VERIFY:\t$device_row[idUser] $device_row[secret]");
			return self::ProcessMoVerifyDevice($robotMsg);
		}
		else
		{	
			$time = $robotMsg->GetCreateTime();

			// update jiwai status
			syslog(LOG_INFO,"UPDATE:\t$device_row[idUser] @$type: $body $time");

			if ( JWSns::UpdateStatus($device_row['idUser'], $body,$type,$time,$isSignature, $serverAddress) )
			{	// succeed posted, keep silence
				return null;
			}
			else
			{	// update error, need quarantine
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

		if ( $user_id )
		{
			$body = <<<_STR_
搞定了！您已经通过了验证。回复本消息即可进行更新，耶！
_STR_;
		}
		else
		{
			$body = <<<_STR_
哎呀！由于您输入的验证码"$secret"不正确，本次验证未能成功，请您查证后再重试一下吧。
_STR_;
		}

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}

	/*
	 *	如果在 Device 表中找不到这个设备，并且发送的也不是机器人命令的话，到这里来注册
	 */
	static public function CreateAccount($robotMsg, $toRegister=false, $idUserConference=null)
	{
		$address = $robotMsg->GetAddress();
		$type	 = $robotMsg->GetType();
		$body	 = $robotMsg->GetBody();
		$serverAddress = $robotMsg->GetServerAddress();

		$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array('address'=>$address,'type'=>$type) ); 

		$last_robot_msg_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWRobotLogic', 'CreateAccount'), $address );

		if ( empty($invitation_id) )
		{
			
			/*
			 * 存下用户注册前发的更新
			 */
			
			/*
			 * 经过测试 发现，奇怪现象，$robotMsg，存入memcache后，取出时，很多属性为空，是不是因为文件不存在了
			 * 导致用户注册前的更新丢失；
			 */
			$memcache = JWMemcache::Instance();
			$memcache->Set( $last_robot_msg_key, array(
						'body' => $body,
						'address' => $address,
						'type' => $type,
						'serverAddress' => $serverAddress,
						), 0, 3600 );

			/*
			 *	1 用户没有被邀请过
			 */

			/*
			 *	为这个用户添加一个缺省的好友：JiWai
			 */
		
			$jiwai_user_db_row = JWUser::GetUserInfo('JiWai');
			
			if ( empty($jiwai_user_db_row) )
				throw new JWException('cant fount user JiWai !?');

			$jiwai_user_id = $jiwai_user_db_row['idUser'];

			$ret = JWInvitation::Create(	 $jiwai_user_id
											,$robotMsg->GetAddress()
											,$robotMsg->GetType()
											,'用户主动上行注册，自动建立邀请'
											,JWDevice::GenSecret(32, JWDevice::CHAR_ALL)
									);

			if ( ! $ret )
			{
				JWLog::LogFuncName(LOG_CRIT, "JWInvitation::Create error for "
									. $robotMsg->GetType() . "://"
									. $robotMsg->GetAddress() 
						);

				return JWRobotLogic::ReplyMsg($robotMsg,"哇，真可怕！现在暂时无法处理新用户请求，您过一会儿再来试试吧。");
			}

			return JWRobotLogic::ReplyMsg($robotMsg,"哇，真可怕！请回复你想用的用户名。");
		}

		/*
		 *	2.0 看看用户是否是看到"请输入用户名"的信息转发过来的，如果不是，提示之。
	 	 */
		if ( ! $toRegister )
			return JWRobotLogic::ReplyMsg($robotMsg,"哇，真可怕！请回复你想用的用户名。");


		/*
		 *	2	用户被邀请过（通过设备查找到邀请）
		 */
		$param_body = $robotMsg->GetBody();

		$user_name	= JWUser::GetPossibleName($param_body, $robotMsg->GetAddress(), $robotMsg->GetType());

//die("[$user_name]");
		if ( empty($user_name) )
			return self::ReplyMsg($robotMsg, "哎呀！您选择的用户名($user_name)太热门了，已经被使用了。请选择另外的用户名回复吧。");

	
		$new_user_row = array	(
							 'nameScreen'	=> $user_name
							,'nameFull'		=> $user_name
							,'pass'			=> JWDevice::GenSecret(16)
							,'isWebUser'	=> 'N'	// 很重要：设备直接注册的用户，要设置标志，方便未来Web上设置密码
						);

		if ( in_array( $type, array('msn','gtalk','newsmth', 'jabbar') ) )
			$new_user_row['email'] = $address;
		
	
		// 增加了 isWebUser 标志，允许用户去 Web 上注册用户
		$new_user_id =  JWUser::Create($new_user_row);

		if ( $new_user_id )
		{
			if ( ! JWSns::CreateDevice($new_user_id, $address, $type, true) )
				JWLog::LogFuncName(LOG_CRIT, "JWDevice::Create($new_user_id,$address,$type,true) failed.");

			// 互相加为好友，标识邀请状态
			JWSns::FinishInvitation($new_user_id, $invitation_id);

			$body = <<<_STR_
欢迎${user_name}！让您的朋友们发送"FOLLOW ${user_name}"来获取您的更新吧。
_STR_;
			/*
			 * 检查用户注册前的更新，将其发出
			 */

			$memcache = JWMemcache::Instance();
			$beforeRegister = $memcache->Get( $last_robot_msg_key );

			if( !empty( $beforeRegister ) && is_array($beforeRegister) )
			{
				$memcache->Del( $last_robot_msg_key );

				//获取用户注册时用的会议用户id，讲会议用户加为自己的好友
				$reply_info = JWSns::GetReplyTo( $new_user_id, $beforeRegister['serverAddress'], $beforeRegister['type'] );
				if( !empty($reply_info) && $reply_info['user_id'] != $new_user_id ){
					JWSns::CreateFriends( $new_user_id, array($reply_info['user_id']) , false );
				}

				//JWSns::UpdateStatus( $new_user_id, $status, $robotMsg->GetType() );
				// 7/24/07 zixia: 如果之前的消息有回复，则返回给用户命令操作的返回，而不是注册成功提示。
				$beforeRegisterMsg = new JWRobotMsg();
				$beforeRegisterMsg->Set( $beforeRegister['address']
						, $beforeRegister['type']
						, $beforeRegister['body']
						, $beforeRegister['serverAddress']
						);
				$reply_msg = self::ProcessMo($beforeRegisterMsg);
				
				if ( ! empty($reply_msg) )
				{
					$reply_msg->SetBody( "${user_name}，您好！" . $reply_msg->GetBody() );
					return $reply_msg;
				}
			}
		}
		else
		{
			$body = <<<_STR_
哇，真可怕！现在暂时无法处理新用户请求，您过一会儿再来试试吧。
_STR_;
		}

		return self::ReplyMsg($robotMsg, $body);
	}


	static public function ReplyMsg($robotMsg, $message)
	{
		$robot_reply_msg = new JWRobotMsg();

		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $message
								, $robotMsg->GetServerAddress()
							);

		return $robot_reply_msg;
	}


}
?>
