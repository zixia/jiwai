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
			 'HELP'		=> array( 'func'=>'Lingo_Help' 	,'param'=>1 )
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
			 'KAI'		=>	'ON'			// alias of ON
			,'START'	=>	'ON'			// alias of ON
			,'WAKE'		=>	'ON'			// alias of ON

			,'GUAN'		=>	'OFF'			// alis of OFF
			,'STOP'		=>	'OFF'			// alis of OFF
			,'SLEEP'	=>	'OFF'			// alis of OFF

			,'INVITE'	=>	'ADD'

			,'NAO'		=>	'NUDGE'

			,'REMOVE'	=>	'DELETE'

			/*
		 	 * 	JiWai扩展
			 */
			,'WOSHISHUI'=>	'WHOAMI'
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
命令：ON、OFF、WHOIS 帐号、NAO 帐号、FOLLOW 帐号、LEAVE 帐号、INVITE 1380013800"
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

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($address_device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$user_id	= $address_device_db_row['idUser'];
		$device_id	= $address_device_db_row['idDevice'];

		
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

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($address_device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$user_id	= $address_device_db_row['idUser'];

		$device_for_user	= JWDevice::GetDeviceRowByUserId($user_id);

		if ( 'sms'==$type && isset($device_for_user['im']) ) {
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

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($address_device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id	= $address_device_db_row['idUser'];


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$followe = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 *	TODO: 权限检查
		 */
		$followe_user_db_row = JWUser::GetUserInfo( $followe );

		JWSns::CreateFollowers($followe_user_db_row['idUser'], array($address_user_id));


		$body = <<<_STR_
每当$followe_user_db_row[nameFull]更新，您都会收到消息。如果要撤销，请发送LEAVE $followe_user_db_row[nameScreen]。发送HELP了解更多。
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

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($address_device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id	= $address_device_db_row['idUser'];

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);


		$followe = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$followe_user_db_row = JWUser::GetUserInfo( $followe );

		JWSns::DestroyFollowers($followe_user_db_row['idUser'], array($address_user_id));


		$body = <<<_STR_
您已离开$followe_user_db_row[nameFull]，取消了对他的订阅。在http://JiWai.de/$followe_user_db_row[nameScreen]/页面上点击订阅或发送FOLLOW $followe_user_db_row[nameScreen]可恢复订阅。
_STR_;

		$robot_reply_msg = new JWRobotMsg();
		
		$robot_reply_msg->Set( $robotMsg->GetAddress()
								, $robotMsg->GetType()
								, $body
							);

		return $robot_reply_msg;
	}


	/*
	 *	当添加用户时，完整地址应为：type://address。如果 type:// 忽略，则按照如下规则：
			1、如果有 type:// 前缀 - 根据 type:// 前缀走
			2、如果没有 type:// 前缀
				2.1 如果 address 包含 @ ，则认为是 type={用户发送消息的type} 的一个 im email 地址。
					比如，用户用 msn 邀请 zixia@zixia.net，则认为 zixia@zixia.net 是 MSN 地址
				2.2 如果 address 以 [\d\+] 打头，并且紧跟着全是数字
					如果是合法手机号码，则 type='sms'
					不是手机号码，认为是 QQ 号码
				2.3	认为是用户 nameScreen
	 */
	static function	Lingo_Add($robotMsg)
	{
		$help = <<<_STR_
ADD命令帮助：ADD 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;


		/*
		 *	获取发送者的 idUser
		 */

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress(
										 $robotMsg->GetAddress()
										,$robotMsg->GetType()
									);


		if ( empty($address_device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id		= $address_device_db_row['idUser'];

		$address_user_row	= JWUser::GetUserDbRowById($address_user_id);


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$user_input_invitee_address 	= $matches[1];

		/*
		 * 用户输入的邀请地址，是否包含类型信息？is full address? 
				(msn://)zixia.net
				(sms://)13911833788
					or 13911833788
				(qq://)918999


				
		 */
		if ( preg_match('#^([^/]+)://(.+)$#',$user_input_invitee_address,$matches) ) 
		{
			/* 
			 *	1、如果有 type:// 前缀 - 根据 type:// 前缀走
			 */
			$invitee_type		= $matches[1];
			$invitee_address	= $matches[2];
		} 
		else 
		{
			/*
			 *	2、如果没有 type:// 前缀
			 */
			$invitee_address	= $user_input_invitee_address;

			if ( preg_match('/@/',$invitee_address) ) 
			{
				/* 
				 *	2.1 如果 address 包含 @ ，则认为是 type={用户发送消息的type} 的一个 im email 地址。
				 *		比如，用户用 msn 邀请 zixia@zixia.net，则认为 zixia@zixia.net 是 MSN 地址
				 */
				$invitee_type		= $robotMsg->GetType();
			} 
			else if ( preg_match('/^[\d\+]?\d+$/', $invitee_address) ) 
			{
				/*
				 *	2.2 如果 address 以 [\d\+] 打头，并且紧跟着全是数字
				 */

				if ( JWDevice::IsValid($invitee_address, 'sms') ) 
				{
					/*
					 *	2.2.1	如果是合法手机号码，则 type='sms'
					 */
					$invitee_type	= 'sms';
				}
				else
				{
					/*
					 *	2.2.2	不是手机号码，认为是 QQ 号码
				 	 */
					$invitee_address= preg_replace('/\+/','',$invitee_address);
					$invitee_type	= 'qq';
				}
			} 	
			else 
			{
				/*
				 *	2.3	认为是用户 nameScreen
						注意：这个类型是多出来的，要排除在设备之外判断
				 */
				$invitee_type	= 'nameScreen';
			}
			/*
			 *	分析完毕
			 */
		}

		/*
		 *	检查
				1、不存在的用户名，并处理好友添加操作
				2、错误的地址和
		 */
		if ( 'nameScreen'==$invitee_type )
		{
			$friend_user_db_row = JWUser::GetUserInfo($invitee_address);
			if ( empty($friend_user_db_row) ) 
			{
				return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！抱歉，我太笨了。您添加的"
												. "${user_input_invitee_address}我不认识，"
												. "请您输入手机号码或邮件地址。了解更多？发送 HELP。"
											);
			}

			$friend_user_id	= $friend_user_db_row['idUser'];

			if ( JWFriend::IsFriend($address_user_id, $friend_user_id) )
			{
				$msg = "您已经是${invitee_address}的好友了。";
			}
			else
			{
				JWSns::CreateFriends	( $address_user_id	,array($friend_user_id) );
				JWSns::CreateFollowers	( $friend_user_id	,array($address_user_id) );

				$msg = <<<_STR_
搞定了！我们已经帮您向${invitee_address}发送了好友添加请求。
_STR_;
			}

			/*
			 *	已经添加用户完毕，返回
			 */
			return JWRobotLogic::ReplyMsg($robotMsg, $msg);

		}
		else if ( ! JWDevice::IsValid($invitee_address,$invitee_type) )
		{
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！抱歉，我太笨了。您添加的 $user_input_invitee_address 我不认识，请您输入手机号码或邮件地址。了解更多？发送 HELP。");
		}


		switch ( $invitee_type )
		{
			case 'sms':
				$invitee_address	= preg_replace("/^\+86/","",$invitee_address);
				break;

			default:
				// 没有动作
		}


		/*
		 *	查看被添加的地址是否已经存在
		 */
		$invitee_device_id 		= JWDevice::GetDeviceIdByAddress(array('address'=>$invitee_address,'type'=>$invitee_type) );
		$invitee_device_db_row	= JWDevice::GetDeviceDbRowById($invitee_device_id);

		if ( !empty($invitee_device_db_row) )
		{
			// 被添加的手机号码已经注册了用户（只是可能还未激活，暂时不考虑错绑定的情况）

			$invitee_user_id = $invitee_device_db_row['idUser'];

			/*
			 * 互相添加为好友和粉丝
			 */
			JWSns::CreateFriends	( $address_user_id	,array($invitee_user_id), true );
			JWSns::CreateFollowers	( $invitee_user_id	,array($address_user_id), true );

			$invitee_user_row 	= JWUser::GetUserDbRowById($invitee_user_id);

			$body = <<<_STR_
${invitee_address}在JiWai注册过！档案地址：http://JiWai.de/$invitee_user_row[nameScreen]/。我们已经帮您发送了好友添加请求。
_STR_;
		}
		else
		{
			/*
			 *	没有注册用户，发送邀请
			 *	使用 msg 数组，区分 email / im 的消息
			 */
			$invite_msg['email'] = <<<_INVITATION_
$address_user_row[nameFull]（$address_user_row[nameScreen]）邀请您来JiWai.de！
_INVITATION_;

			$invite_msg['im'] = $invite_msg['email'] . <<<_INVITATION_
请回复您名字的拼音，这样我们可以帮助您完成注册。（本短信服务免费）
_INVITATION_;
			$invite_msg['sms'] = $invite_msg['im'];

			JWSns::Invite($address_user_id, $invitee_address, $invitee_type, $invite_msg);

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


		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);
		$address_user_id		= $address_device_db_row['idUser'];

		if ( empty($address_user_id) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_row	= JWUser::GetUserDbRowById($address_user_id);


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$friend_name = $matches[1];

		/*
		 *	获取被删除者的用户信息
		 */
		$friend_user_row = JWUser::GetUserInfo( $friend_name );

		JWSns::DestroyFriends	($address_user_id			,array($friend_user_row['idUser']));
		JWSns::DestroyFollowers	($friend_user_row['idUser']	,array($address_user_id));


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
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$friend_name = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$friend_user_db_row = JWUser::GetUserInfo( $friend_name );

		if ( empty($friend_user_db_row) )
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");

		$status_ids	= JWStatus::GetStatusIdsFromUser($friend_user_db_row['idUser'], 1);

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
$friend_user_db_row[nameScreen]: $status
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
			return JWRobotLogic::ReplyMsg($robotMsg, $help);


		$friend_name 		= $matches[1];
		$friend_user_db_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_db_row) )
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");

		$friend_user_id		= $friend_user_db_row['idUser'];

		$send_via_device	= JWUser::GetSendViaDeviceByUserId($friend_user_id);

		if ( 'none'==$send_via_device )
			return JWRobotLogic::ReplyMsg($robotMsg, "$friend_user_db_row[nameFull]现在不想被挠挠。。。要不稍后再试吧？");

		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	


		$address_device_id		= JWDevice::GetDeviceIdByAddress( array('address'=>$address,'type'=>$type) );
		$address_device_db_row 	= JWDevice::GetDeviceDbRowById	($address_device_id);

		$address_user_id		= $address_device_db_row['idUser'];

		if ( ! JWFriend::IsFriend($friend_user_db_row['idUser'], $address_user_id) )
			return JWRobotLogic::ReplyMsg($robotMsg, "对不起，你还不是$friend_user_db_row[nameFull]的好友呢，"
											."不能随便挠挠他，呵呵。等他加你为好友再挠吧!");

		$address_user_db_row	= JWUser::GetUserDbRowById($address_user_id);


		$nudge_message = <<<_NUDGE_
$address_user_db_row[nameScreen]挠挠了你一下，提醒你更新JiWai！回复本消息既可更新你的JiWai。
_NUDGE_;

		JWNudge::NudgeUserIds(array($friend_user_db_row['idUser']), $nudge_message, 'nudge');

		$body = <<<_STR_
我们已经帮您挠挠了$friend_user_db_row[nameScreen]一下！期待很快能得到您朋友的回应。
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
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$friend_name 		= $matches[1];
		$friend_user_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_row['idUser']) )
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");


		$register_date	= date("Y年n月",strtotime($friend_user_row['timeCreate']));
	
		$body = <<<_STR_
$friend_user_row[nameFull]，注册时间：$register_date
_STR_;

		if ( !empty($friend_user_row['bio']) )
			$body .= "，简介：$friend_user_row[bio]";

		if ( !empty($friend_user_row['location']) )
			$body .= "，位置：$friend_user_row[location]";

		if ( !empty($friend_user_row['url']) )
			$body .= "，网站：$friend_user_row[url]";


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
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$inviter_name 		= $matches[1];
		$inviter_user_row 	= JWUser::GetUserInfo( $inviter_name );


		/*
		 *	检查发送者是否已经注册 
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/*
		 *	分为三种情况处理：
				1、用户已经注册
				2、用户没有注册，但是有邀请
				3、用户没有注册，没有邀请
		 */
		if ( ! empty($address_device_db_row) )
		{
			/*
			 *	 1、用户已经注册
			 */

			$address_user_id	= $address_device_db_row['idUser'];


			// 互相加，无所谓先后顺序
			JWSns::CreateFriends	($address_user_id, array($inviter_user_row['idUser']), true);
			JWSns::CreateFollowers	($address_user_id, array($inviter_user_row['idUser']), true);

			$body = <<<_STR_
搞定了！您和$inviter_user_row[nameFull]($inviter_user_row[nameScreen])已经成为好友！回复GET $inviter_user_row[nameScreen]查看最新更新。
_STR_;

		}
		else if ( !empty($inviter_user_row) )
		{
			/*
			 *	2、 被邀请用户没有完成注册 
			 * 		这时用户回复的字符串，只要不是命令，即会被系统当作用户选择的用户名。
			 *		回复提示信息
			 */

			$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array('address'=>$address,'type'=>$type) ); 


			if ( empty($invitation_id) )
				return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！${inviter_name}没有邀请过您。请回复您希望使用的用户名。");


			JWSns::AcceptInvitation($invitation_id);

			return JWRobotLogic::ReplyMsg($robotMsg, "搞定了！您已经接受了${inviter_name}的邀请。请回复您希望使用的用户名。");
		}
		else
		{
			/*
				3、无效邀请
			 *		邀请者(Accept的用户)不存在？
			 */
			$body = <<<_STR_
哎呀！没有找到这个用户($inviter_name)，是不是他（她）改名了？。发送HELP了解更多。
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
	static function	Lingo_Deny($robotMsg)
	{
		$help = <<<_HELP_
DENY命令帮助：DENY 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_HELP_;

		$param_body = $robotMsg->GetBody();

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);


		$friend_name 	= $matches[1];

		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType() ;
		$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array(
									 'address'	=> $address
									,'type'		=> $type
								) ); 

		if ( empty($invitation_id) )
		{
			/*
			 * 没有邀请过这个设备
			 * 检查是否设备已经注册过，如果没有注册过则引导注册
			 */
			if ( JWDevice::IsExist($address,$type) )
				return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！${friend_name}并没有邀请您。发送HELP了解更多。");
			else
				return JWRobotLogic::CreateAccount($robotMsg);
		}

		/*
		 *	删除邀请记录
		 *	FIXME: 如果一个 address 被多人邀请多次，这里可能删除的是别人的邀请……
					这样需要多 deny 几次，就全部删除了……
		 */
		JWInvitation::Destroy($invitation_id);

		$friend_db_row = JWUser::GetUserInfo($friend_name);

		if ( empty($friend_db_row) )
		{
			$body = <<<_STR_
哎呀！没有找到邀请您的朋友${friend_name}，发送HELP或访问http://jiwai.de/了解更多！
_STR_;
		}
		else
		{
			$body = <<<_STR_
搞定了！您没有接受$friend_db_row[nameFull]($friend_db_row[nameScreen])的邀请。发送GET $friend_db_row[nameScreen]获取$friend_db_row[nameFull]的最新更新（本短信服务免费）
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

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id	= $device_db_row['idUser'];

		if ( empty($address_user_id) )
		{
			// 可能 device 还在，但是用户没了。
			// 删除 device.
			JWDevice::Destroy($device_db_row['idDevice']);
			return JWRobotLogic::CreateAccount($robotMsg);
		}

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
