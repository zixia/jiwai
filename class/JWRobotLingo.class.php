<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class
 */
class JWRobotLingo {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingo
	 */
	static private $msInstance;

	/*
	 *	记录所有的机器人命令，与函数的对应表。（Lingo函数以 Lingo_ 打头）
	 *	param 设置这个命令接受的最多参数。如果用户输入多于这个最大值，则不当作lingo处理。（如用户输入"on the way home"）
	 */
	static private $msRobotLingo = array (
			 'HELP'		=> array( 'func'=>'Lingo_Help' 	,'param'=>0 )
			,'TIPS'		=> array( 'func'=>'Lingo_Tips' 	,'param'=>0 )

			,'ON'		=> array( 'func'=>'Lingo_On' 	,'param'=>0)
			,'OFF'		=> array( 'func'=>'Lingo_Off' 	,'param'=>0)

			,'FOLLOW'	=> array( 'func'=>'Lingo_Follow','param'=>1)
			,'LEAVE'	=> array( 'func'=>'Lingo_Leave' ,'param'=>1)

			,'ADD'		=> array( 'func'=>'Lingo_Add' 	,'param'=>1)
			,'DELETE'	=> array( 'func'=>'Lingo_Delete','param'=>1)

			,'GET'		=> array( 'func'=>'Lingo_Get' 	,'param'=>1)
			,'NUDGE'	=> array( 'func'=>'Lingo_Nudge' ,'param'=>1)
			,'WHOIS'	=> array( 'func'=>'Lingo_Whois' ,'param'=>1)

			,'ACCEPT'	=> array( 'func'=>'Lingo_Accept','param'=>1)
			,'DENY'		=> array( 'func'=>'Lingo_Deny' 	,'param'=>1)

			,'D'		=> array( 'func'=>'Lingo_D' 	,'param'=>999)


			/*
			 *	JiWai 扩展，Twitter没有
			 */
			,'WHOAMI'	=> array( 'func'=>'Lingo_Whoami','param'=>0)
		);


	/*
	 *	记录所有的机器人命令的alias
	 */
	static private $msRobotLingoAlias = array (
			 'START'	=>	'ON'			// alias of ON
			,'WAKE'		=>	'ON'			// alias of ON

			,'STOP'		=>	'OFF'			// alis of OFF
			,'SLEEP'	=>	'OFF'			// alis of OFF

			,'INVITE'	=>	'ADD'

			,'NAO'		=>	'NUDGE'

			,'REMOVE'	=>	'DELETE'
		);


	/**
	 * Instance of this singleton class
	 *
	 * @return JWRobotLingo
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
	 *	判断一个 RobotMsg 是否为 Lingo Msg
	 *	@param	JWRobotMsg	$robotMsg
	 *	@return	array		Lingo Msg 的对应处理函数（通过call_user_func调用）
				false		不是 lingo Msg
	 */
	static public function GetLingoFunctionFromMsg($robotMsg)
	{
		if ( empty($robotMsg) )
			throw new JWException('null param?');

		$body = $robotMsg->GetBody();

		if ( ! preg_match('/^([[:alpha:]]+)\s*(\w*)/',$body,$matches) ) 
			return false;

		$lingo 	= strtoupper($matches[1]);
		$param	= $matches[2];


		if ( isset(self::$msRobotLingoAlias[$lingo]) ) {
			// it's a alias lingo
			$lingo = self::$msRobotLingoAlias[$lingo];
		} else if ( isset(self::$msRobotLingo[$lingo]) ) {
			// it's a lingo name, pass.
			;
		} else {
			// no such lingo
			return false;
		}



		$lingo_info	= self::$msRobotLingo[$lingo];

		if ( empty($param) )
			$param_count = 0;
		else
			$param_count = count( preg_split('/\s+/',$param) );


	 	/* 	lingo_info[param] 设置这个命令接受的最多参数
		 *	如果用户输入多于这个最大值，则不当作lingo处理。
		 * 	（如用户输入"on the way home"）
		 */
//echo "Lingo: $lingo, param: $param param_count: $param_count lingo_info['param']=" . $lingo_info['param'] . "\n";
		if ( $param_count > $lingo_info['param'] )
			return false;

//echo "lingo_info: " . $lingo_info['func'] . "\n";
		$lingo_function = array('JWRobotLingo', $lingo_info['func']);

		if ( ! is_callable($lingo_function) )
		{
			JWLog::Log(LOG_ERR, "JWRobotLingo::GetLingoFunctionFromMsg found lingo[$lingo] is unimpl");
			return false;
		}

		return $lingo_function;
	}
	

	static public function ErrorMsg($robotMsg, $message)
	{
		$robot_reply_msg = new JWRobotMsg();

		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $message
							);

		return $robot_reply_msg;
	}

	/*
	 *
	 */
	static function	Lingo_Help($robotMsg)
	{
		$body = <<<_STR_
告诉JiWai这一刻你在做什么，我们会让你的朋友们知道！想要更多帮助，回复TIPS。
_STR_;

		if ( 'sms'==$robotMsg->GetType() )
			$body .= '本短信服务免费。';

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Tips($robotMsg)
	{
		$body = <<<_STR_
命令：ON、OFF、WHOIS 帐号、NAO 帐号、FOLLOW 帐号、LEAVE 帐号、INVITE "
_STR_;

		if ( 'sms'==$robotMsg->GetType() )
			$body .= "手机号";
		else
			$body .= "帐号";

		$body .="。了解更多？登录 http://jiwai.de ！";

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_On($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_ids		= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );

		if ( empty($address_device_ids) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_device_rows	= JWDevice::GetDeviceAddressRowsByIds($address_device_ids);

		$user_id	= $address_device_rows[$type][$address]['idUser'];
		$device_id	= $address_device_rows[$type][$address]['idDevice'];

		
		if ( 'sms'==$type )
			$ret = JWUser::SetSendViaDevice($user_id, 'sms');
		else
			$ret = JWUser::SetSendViaDevice($user_id, 'im');
			
		if ( ! $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_On JWUser::SetSendViaDevice($user_id, ...) failed");


		$ret = JWDevice::SetDeviceEnabledFor($device_id, 'everything');

		if ( ! $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_On JWDevice::SetDeviceEnabledFor($device_id,...) failed");


		$body = <<<_STR_
搞定了！你在做什么呢？任何时候发送OFF都可以随时关掉通知。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $address , $type , $body );

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Off($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_ids		= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );

		if ( empty($address_device_ids) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_device_rows	= JWDevice::GetDeviceAddressRowsByIds($address_device_ids);


		$user_device_row		= $address_device_rows[$type][$address];
		$user_id				= $user_device_row['idUser'];
		$device_id				= $user_device_row['idDevice'];


		if ( 'sms'==$type && isset($user_device_row['im']) ) {
			// 如果关闭的是 sms，而用户又有 im 帐号绑定
			$ret = JWUser::SetSendViaDevice($user_id, 'im');
		} else {
			$ret = JWUser::SetSendViaDevice($user_id, 'none');
		}
			
		if ( ! $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_Off JWUser::SetSendViaDevice($user_id,...) failed");


		$body = <<<_STR_
通知消息已关闭。发送ON可开启。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $address , $type , $body );

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Follow($robotMsg)
	{
		$help = <<<_STR_
FOLLOW命令帮助：FOLLOW 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;

		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_ids		= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );

		if ( empty($address_device_ids) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_device_rows	= JWDevice::GetDeviceAddressRowsByIds($address_device_ids);


		$address_user_id	= $address_device_rows[$type][$address]['idUser'];


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$followe = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 *	TODO: 权限检查
		 */
		$followe_user_row = JWUser::GetUserInfo( $followe );

		JWSns::CreateFollowers($followe_user_row['idUser'], array($address_user_id));


		$body = <<<_STR_
每当$followe_user_row[nameFull]更新，您都会收到消息。如果要撤销，请发送LEAVE $followe_user_row[nameScreen]。发送HELP了解更多。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Leave($robotMsg)
	{
		$help = <<<_STR_
LEAVE命令帮助：LEAVE 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;


		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_ids	= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );
		$address_device_rows= JWDevice::GetDeviceAddressRowsByIds( $address_device_ids );

		$address_user_id	= @$address_device_rows[$type][$address]['idUser'];


		if ( empty($address_user_id) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_rows	= JWUser::GetUserDbRowsByIds(array($address_user_id));
		$address_user_row	= $address_user_rows[$address_user_id];

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$followe = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$followe_user_row = JWUser::GetUserInfo( $followe );

		JWSns::DestroyFollowers($followe_user_row['idUser'], array($address_user_row['idUser']));


		$body = <<<_STR_
您已离开$followe_user_row[nameFull]，取消了对他的订阅。在http://JiWai.de/$followe_user_row[nameScreen]/页面上点击订阅或发送FOLLOW ZIXIA可恢复订阅。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *	目前只支持 add 手机号码
	 */
	static function	Lingo_Add($robotMsg)
	{
		$help = <<<_STR_
ADD命令帮助：ADD 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;


		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_ids	= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );
		$address_device_rows= JWDevice::GetDeviceAddressRowsByIds($address_device_ids);

		$address_user_id	= $address_device_rows[$type][$address]['idUser'];

		if ( empty($address_user_id) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_rows	= JWUser::GetUserDbRowsByIds(array($address_user_id));
		$address_user_row	= $address_user_rows[$address_user_id];


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\+?\d+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$invitee_sms_number = $matches[1];

		$invitee_sms_number	= preg_replace("/^\+86/","",$invitee_sms_number);

		/*
		 *	查看被添加的手机号码是否已经存在
		 */
		$invitee_device_ids 	= JWDevice::GetDeviceIdsByAddresses(
											array( array('address'=>$invitee_sms_number,'type'=>'sms') )
										);
		$invitee_device_rows 	= JWDevice::GetDeviceAddressRowsByIds($invitee_device_ids);

		if ( isset($invitee_device_rows['sms'][$invitee_sms_number]) )
		{
			// 被添加的手机号码已经注册了用户（只是可能还未激活，暂时不考虑错绑定的情况）

			$invitee_user_id = $invitee_device_rows['sms'][$invitee_sms_number]['idUser'];

			JWSns::CreateFriends	( $address_user_id	,array($invitee_user_id) );
			JWSns::CreateFollowers	( $invitee_user_id	,array($address_user_id) );

			$invitee_user_rows 	= JWUser::GetUserDbRowsByIds(array($invitee_user_id));
			$invitee_user_row 	= $invitee_user_rows[$invitee_user_id];

			$body = <<<_STR_
${invitee_sms_number}在JiWai注册过！档案地址：http://JiWai.de/$invitee_user_row[nameScreen]/。我们已经帮您发送了好友添加请求。
_STR_;
		}
		else
		{
			// 没有注册用户，发送邀请
			$invite_msg = <<<_INVITATION_
$address_user_row[nameFull]($address_user_row[nameScreen])想成为您在JiWai的好友！回复ACCEPT $address_user_row[nameScreen]接受，或回复DENY $address_user_row[nameScreen]拒绝。
_INVITATION_;
			JWSns::Invite($address_user_id, $invitee_sms_number, 'sms', $invite_msg);

			$body = <<<_STR_
搞定了！我们已经帮您发出了邀请！期待很快能得到您朋友的回应。
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
	 *
	 */
	static function	Lingo_Delete($robotMsg)
	{
		$help = <<<_STR_
DELETE命令帮助：DELETE 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;


		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	


		$address_device_ids	= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );
		$address_device_rows= JWDevice::GetDeviceAddressRowsByIds($address_device_ids);

		$address_user_id	= $address_device_rows[$type][$address]['idUser'];
		$address_user_rows	= JWUser::GetUserDbRowsByIds(array($address_user_id));
		$address_user_row	= $address_user_rows[$address_user_id];


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$friend_name = $matches[1];

		/*
		 *	获取被删除者的用户信息
		 */
		$friend_user_row = JWUser::GetUserInfo( $friend_name );

		JWSns::DestroyFriends	($address_user_id, 	array($friend_user_row['idUser']));
		JWSns::DestroyFollowers	($address_user_id, 	array($friend_user_row['idUser']));


		$body = <<<_STR_
搞定了！$friend_user_row[nameFull]($friend_user_row[nameScreen])已经不再是您的好友了。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Get($robotMsg)
	{
		$help = <<<_STR_
使用方法：GET 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$friend_name = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$friend_user_row = JWUser::GetUserInfo( $friend_name );

		if ( empty($friend_user_row['idUser']) )
			return self::ErrorMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");

		$status_ids	= JWStatus::GetStatusIdsFromUser($friend_user_row['idUser'], 1);

		if ( empty($status_ids) )
		{
			$status = '还没有更新过';
		}
		else
		{
			$status_id		= $status_ids['status_ids'][0];

			$status_rows	= JWStatus::GetStatusDbRowsByIds ( array($status_id) );
			$status_row		= $status_rows[$status_id];
			$status	= $status_row['status'];
		}

		$body = <<<_STR_
$friend_user_row[nameScreen]: $status
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Nudge($robotMsg)
	{
		$help = <<<_STR_
使用方法：NAO 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$friend_name 		= $matches[1];
		$friend_user_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_row['idUser']) )
			return self::ErrorMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");

		$friend_user_id		= $friend_user_row['idUser'];

		$send_via_device_rows	= JWUser::GetSendViaDeviceRowByIds( array($friend_user_id) );

		if ( 'none'==$send_via_device_rows[$friend_user_id] )
			return self::ErrorMsg($robotMsg, "$friend_user_row[nameFull]现在不想被挠挠。。。要不稍后再试吧？");

		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	


		$address_device_ids	= JWDevice::GetDeviceIdsByAddresses(	array( 
													array('address'=>$address,'type'=>$type) 
											) );
		$address_device_rows= JWDevice::GetDeviceAddressRowsByIds($address_device_ids);

		$address_user_id	= $address_device_rows[$type][$address]['idUser'];

		if ( ! JWFriend::IsFriend($friend_user_row['idUser'], $address_user_id) )
			return self::ErrorMsg($robotMsg, "对不起，你还不是$friend_user_row[nameFull]的好友呢，"
											."不能随便挠挠他，呵呵。等他加你为好友再挠吧!");

		$address_user_rows	= JWUser::GetUserDbRowsByIds(array($address_user_id));
		$address_user_row	= $address_user_rows[$address_user_id];


		$nudge_message = <<<_NUDGE_
$address_user_row[nameScreen]挠挠了你一下，提醒你更新JiWai！回复本消息既可更新你的JiWai。
_NUDGE_;

		JWNudge::NudgeUserIds(array($friend_user_row['idUser']), $nudge_message, 'nudge');

		$body = <<<_STR_
我们已经帮您挠挠了$friend_user_row[nameScreen]一下！期待很快能得到您朋友的回应。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}



	/*
	 *
	 */
	static function	Lingo_Whois($robotMsg)
	{
		$help = <<<_STR_
使用方法：WHOIS 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$friend_name 		= $matches[1];
		$friend_user_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_row['idUser']) )
			return self::ErrorMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");


		$register_date	= date("Y年n月",strtotime($friend_user_row['timeCreate']));
	
		$body = <<<_STR_
$friend_user_row[nameFull]；注册时间：$register_date
_STR_;

		if ( !empty($friend_user_row['bio']) )
			$body .= "；简介：$friend_user_row[bio]";

		if ( !empty($friend_user_row['location']) )
			$body .= "；位置：$friend_user_row[location]";

		if ( !empty($friend_user_row['url']) )
			$body .= "；网站：$friend_user_row[url]";


		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Accept($robotMsg)
	{
		$help = <<<_STR_
ACCEPT命令帮助：ACCEPT 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return self::ErrorMsg($robotMsg, $help);

		$inviter_name = $matches[1];

		/*
		 *	获取邀请者的用户信息
		 */
		$inviter_user_row = JWUser::GetUserInfo( $inviter_name );

		if ( empty($inviter_user_row) )
			return self::ErrorMsg($robotMsg, $help);


		/*
		 *	检查发送者是否已经注册 
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_ids		= JWDevice::GetDeviceIdsByAddresses( array( 
													array('address'=>$address,'type'=>$type) 
											) ); 
		$address_device_rows	= JWDevice::GetDeviceAddressRowsByIds( $address_device_ids );

		// 可能没有，因为没有注册
		$address_user_id	= @$device_rows[$type][$address]['idUser'];

		/*
		 *	分为几种情况处理：
				1、被邀请用户没有注册
					1.1	用户被邀请过（通过设备查找到邀请）
					1.2 用户没有被邀请过
				2、被邀请用户已经注册（通过设备查找到了用户）
		 *
		 */
		if ( empty($address_user_id) )
		{
die("UN-IMPL");
			// 1、用户没有注册，查找邀请
			$invitation_ids	= JWInvitation::GetInvitationIdsFromAddresses( array( 
													array('address'=>$address,'type'=>$type) 
											) ); 

			if ( isset($invitation_ids) )
			{
				/*
				 *	1.1	用户被邀请过（通过设备查找到邀请）
				 */
				JWSns::AcceptInvitation( $idUser, array_shift($invitation_ids[0]) );
			}
			else
			{
				/*
				 *	1.2 用户没有被邀请过
				 */
			}
		}
		else
		{
			// 2、用户已经注册
			JWSns::CreateFriends	($address_user_id, $inviter_user_row['idUser'], true);
			JWSns::CreateFollowers	($address_user_id, $inviter_user_row['idUser'], true);
		}


		$body = <<<_STR_
搞定了！ 您和$invitee_user_row[nameFull]($address_user_row[nameScreen])已经成为好友！回复GET $invitee_user_row[nameScreen]查看最新更新。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Deny($robotMsg)
	{
		$body = <<<_STR_
尚未支持，请明天再试吧。http://JiWai.de/
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_D($robotMsg)
	{
		$body = <<<_STR_
尚未支持，请明天再试吧。http://JiWai.de/
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *
	 */
	static function	Lingo_Whoami($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();

		$device_row = JWDevice::GetDeviceRowByAddress($address,$type);

		if ( empty($device_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id	= $device_row['idUser'];
		$address_user_row	= JWUser::GetUserInfo($address_user_id);
	
		$body = <<<_STR_
$address_user_row[nameFull] ($address_user_row[nameScreen])
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
