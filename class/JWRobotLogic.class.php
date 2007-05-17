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
			JWLog::Instance()->Log(LOG_CRIT, 'JWRobotLogic::process_mo received a empty msg' );
			return null;
		}
		
		if ( ! $robotMsg->IsValid() )
		{
			JWLog::Instance()->Log(LOG_CRIT, 'JWRobotLogic::process_mo received a invalid msg' );
			return false;
		}

		$address	= $robotMsg->GetAddress();
		$type		= $robotMsg->GetType();
		$body 		= $robotMsg->GetBody();

		$msg = sprintf("%-35s: %s\n", "MO($type://$address)", $body);
		echo iconv('UTF-8', 'GBK', $msg);

		/*
		 *	一个 MO 消息有如下几种状态：
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
		$lingo_func		= JWRobotLingo::GetLingoFunctionFromMsg($robotMsg);

		if ( !empty($lingo_func) )
		{
			$reply_robot_msg 	= call_user_func($lingo_func, $robotMsg);
		}
		else if ( JWDevice::IsExist($address, $type, false) )
		{
			// 设备已经设置，(false 代表包含未激活的设备)
			// 		1、user JiWai status
			//		2、verify code
			$reply_robot_msg	= self::ProcessMoStatus($robotMsg);
		}
		else
		{
			// 非注册用户（在Device表中没有的设备）
			$reply_robot_msg 	= self::CreateAccount($robotMsg);
		}

		if ( empty($reply_robot_msg) ) {
			$msg = "MT: none\n";
		} else {
			$msg = sprintf("%-35s: %s\n", "MT(" . $reply_robot_msg->GetType()
									. "://" . $reply_robot_msg->GetAddress()
									. ")"
							, $reply_robot_msg->GetBody() );
		}
		echo iconv('UTF-8', 'GBK', $msg);

		return $reply_robot_msg;
	}


	static function ProcessMoStatus($robotMsg)
	{

		$address	= $robotMsg->GetAddress();
		$type		= $robotMsg->GetType();
		$body		= $robotMsg->GetBody();


		$user_state = JWDevice::GetUserStateFromDevice($address,$type);

		if ( empty($user_state) )
		{	
			// user not registed
			JWLog::Instance()->Log(LOG_NOTICE,"UNKNOWN IM: $type://$address [$body]");
			return JWRobotLingo::Lingo_Tips($robotMsg);
		}
		else if ( ! empty($user_state['secret']) )
		{	
			// device not verified
			JWLog::Instance()->Log(LOG_INFO,"VERIFY:\t$user_state[idUser] $user_state[secret]");
			return self::ProcessMoVerifyDevice($robotMsg);
		}
		else
		{	
			// update jiwai status
			syslog(LOG_INFO,"UPDATE:\t$user_state[idUser] @$type: $body");
			if ( JWSns::UpdateStatus($user_state['idUser'], $body, $type ) )
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
搞定了！您已经通过了叽歪de验证。约1分钟后您就可以通过 ${type} 发送更新了，耶！
_STR_;
//(这一刻，你在做什么？ - http://JiWai.de)
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
	static public function CreateAccount($robotMsg)
	{
	}
}
?>
