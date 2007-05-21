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

			if ( JWSns::UpdateStatus($device_row['idUser'], $body, $type, $time ) )
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
搞定了！您已经通过了叽歪de验证。约1分钟后您就可以通过${type}发送更新了，耶！
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
		$address = $robotMsg->GetAddress();
		$type	 = $robotMsg->GetType();

		$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array('address'=>$address,'type'=>$type) ); 

		if ( empty($invitation_id) )
		{
			/*
			 *	1 用户没有被邀请过
			 */

			/*
			 *	为这个用户添加一个缺省的好友：JiwWi
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

			return JWRobotLogic::ReplyMsg($robotMsg,"哇，真可怕！请回复你想用的JiWai用户名。访问http://jiwai.de/了解更多。");
		}


		/*
		 *	2	用户被邀请过（通过设备查找到邀请）
		 */
		$param_body = $robotMsg->GetBody();

		$user_name = preg_replace("/[^\w]+/"	,""	,$param_body);
		$user_name = preg_replace("/^\d+/"		,""	,$user_name);
		
		if ( empty($user_name) )
		{
			// 从邮件中取用户名，并进行特殊字符处理
			$user_name = $robotMsg->GetAddress();
			$user_name = preg_replace("/@.*/"	,""	,$user_name);
			$user_name = preg_replace("/\./"	,""	,$user_name);

			// 如果是手机用户或者QQ用户 
			if ( preg_match('/^\d+$/',$user_name) )
			{
				if ( JWDevice::IsValid($user_name,'sms') )
					$user_name = 'sms' . $user_name;
				else
					$user_name = 'qq' . $user_name;
			}
			else
			{
				$user_name = preg_replace("/^\d+/"	,""	,$user_name);
			}
		}
		

		/*
		 *	处理名字过短的问题
		 *	如果是3个字符的名字，那么通过
		 *	如果是1、2个字符的名字，则随机填充到4个字符
		 */
		$user_name_len = strlen($user_name);

		if ( 3>$user_name_len )
		{
			for ( $n=$user_name_len; $n<4; $n++ )
				$user_name .= rand(0,9);
		}

		$is_valid_name = false;

		if ( ! JWUser::IsExistName($user_name) )
		{
			$is_valid_name = true;
		}
		else
		{
			$n = 1;
			while ( $n++ < 30 )
			{
				if ( ! JWUser::IsExistName("$user_name$n") )
				{
					$user_name .= $n;

					$is_valid_name = true;
					break;
				}
			}
			
		}

		if ( ! $is_valid_name )
		{
			$month_day = date("md");
			if ( ! JWUser::IsExistName("$user_name$month_day") )
			{
				$user_name 	.= $month_day;

				$is_valid_name = true;
			}
		}

		if ( ! $is_valid_name )
			return self::ReplyMsg($robotMsg, "哎呀！您选择的用户名($user_name)太热门了，已经被使用了。请选择另外的用户名回复吧。");

	
		$new_user_row = array	(
							 'nameScreen'	=> $user_name
							,'nameFull'		=> $user_name
							,'pass'			=> JWDevice::GenSecret(16)
							,'isWebUser'	=> 'N'	// 很重要：设备直接注册的用户，要设置标志，方便未来Web上设置密码
						);

		if ( 'qq'!=$type && 'sms'!=$type )
			$new_user_row['email'] = $address;
		
	
		// TODO 增加标志，允许用户去 Web 上注册用户
		$new_user_id =  JWUser::Create($new_user_row);

		if ( $new_user_id )
		{
			if ( ! JWSns::CreateDevice($new_user_id, $address, $type, true) )
				JWLog::LogFuncName(LOG_CRIT, "JWDevice::Create($new_user_id,$address,$type,true) failed.");

			// 互相加为好友，标识邀请状态
			JWSns::FinishInvitation($new_user_id, $invitation_id);

			$body = <<<_STR_
欢迎${user_name}！让您的朋友们发送"FOLLOW ${user_name}"来获取您的更新吧。发送HELP可以了解更多JiWai功能。 
_STR_;
		}
		else
		{
			$body = <<<_STR_
哇，真可怕！现在暂时无法处理新用户请求，您过一会儿再来试试吧。");
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
							);

		return $robot_reply_msg;
	}


}
?>
