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


	static private $msRobotCommand = array (
			'help'		=>	'Command_Help'
			, 'intro'		=>	'Command_Intro'
			);


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

		$body = $robotMsg->GetBody();

		/*
		 *	一个 MO 消息有如下几种状态：
				1、用户从来没有用过，发送给特服号码一条短信
				2、用户从来没有用过，发送给特服号码一条短信后，得到输入用户名的提示，又发回来了用户名
				3、用户是被邀请来的，发送了accept/deny
				4、用户设备没有经过验证
				5、用户设备已经通过验证
		 *
		 *
		 *
		 */
		$lingo_func = JWRobotLingo::GetLingoFunctionFromMsg($robotMsg);

		if ( !empty($lingo_func) )
		{
echo "\nfound lingo\n";
			return call_user_func($lingo_func, $robotMsg);
		}
		else // user JiWai status or verify code
		{
echo "\nnot lingo\n";
			return self::ProcessMoStatus($robotMsg);
		}
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
			return Command_Intro($robotMsg);
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
:-D 恭喜，您已经通过了叽歪de验证！约1分钟后您就可以通过 ${type} 发送更新了！ 耶！
_STR_;
//(这一刻，你在做什么？ - http://JiWai.de)
		}
		else
		{
			$body = <<<_STR_
:-( 非常抱歉，由于您输入的验证码"$secret"不正确，本次未能验证成功，请查证后重试。 哼叽...
_STR_;
		}

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}

}
?>
