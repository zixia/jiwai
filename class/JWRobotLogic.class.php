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
	 * 		array of JWRobotMsg		- reply many msg
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
			return false;

		$body = $robotMsg->GetBody();

		if ( preg_match('/^(\w+)/',$body,$matches) 
				&& isset(self::$msRobotCommand[strtolower($matches[1])]) )
		{
			$cmd = strtolower($matches[1]);

			// user JiWai command
			return call_user_func(array('JWRobotLogic', self::$msRobotCommand[$cmd]), $robotMsg);
		}
		else // user JiWai status or verify code
		{
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
		{	// user not registed
			JWLog::Instance()->Log(LOG_NOTICE,"UNKNOWN IM: $type://$address [$body]");
			return self::Command_Intro($robotMsg);
		}
		else if ( ! empty($user_state['secret']) )
		{	// device not verified
			JWLog::Instance()->Log(LOG_INFO,"VERIFY:\t$user_state[idUser] $user_state[secret]");
			return self::ProcessMoVerifyDevice($robotMsg);
		}
		else
		{	// update jiwai status
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

		if ( JWSns::VerifyDevice($robotMsg->GetAddress()
								, $type
								, $secret
								) )
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


	static function Command_Help($robotMsg)
	{
		$body = <<<_STR_
命令列表： help:帮助 intro:简介
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}

	static function Command_Intro($robotMsg)
	{
		$body = <<<_STR_
这一刻，你在做什么？ 免费注册叽歪de - http://JiWai.de
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}

}
?>
