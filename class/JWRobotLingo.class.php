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
			,'NAONAO'	=>	'NUDGE'
			,'NN'		=>	'NUDGE'

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
		$body = self::ConvertCorner( $body );

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
告诉JiWai这一刻您在做什么，我们会让您的朋友们知道！想要更多帮助，回复TIPS。
_STR_;
		$body = <<<_STR_
告诉我们这一刻您在做什么，我们会让您的朋友们知道！想要更多帮助，回复TIPS。
_STR_;

		if ( 'sms'==$robotMsg->GetType() )
			$body .= '本短信服务免费。';

		return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Tips($robotMsg)
	{
		$body = <<<_STR_
命令：ON、OFF、WHOIS帐号、NN帐号、FOLLOW帐号、LEAVE帐号、ADD帐号。了解更多？登录http://jiwai.de ！
_STR_;
		$body = <<<_STR_
命令：ON、OFF、WHOIS帐号、NN帐号、FOLLOW帐号、LEAVE帐号、ADD帐号。了解更多？
_STR_;

		return JWRobotLogic::ReplyMsg($robotMsg, $body);
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

		
		$ret = JWUser::SetSendViaDevice($user_id, $type);
			
		if ( ! $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_On JWUser::SetSendViaDevice($user_id,$type ...) failed");


		$ret = JWDevice::SetDeviceEnabledFor($device_id, 'everything');

		if ( ! $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_On JWDevice::SetDeviceEnabledFor($device_id,...) failed");


		$body = <<<_STR_
搞定了！您在做什么呢？任何时候发送OFF都可以随时关掉通知。
_STR_;

		return JWRobotLogic::ReplyMsg($robotMsg, $body);

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

		$ret = JWUser::SetSendViaDevice($user_id, 'web');
			
		if ( ! $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_Off JWUser::SetSendViaDevice($user_id,'web'...) failed");


		$body = <<<_STR_
通知消息已关闭。发送ON可开启。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$body = <<<_STR_
通知消息已关闭。发送ON可开启。
_STR_;

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Follow($robotMsg)
	{
		$help = <<<_STR_
FOLLOW命令帮助：FOLLOW 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
FOLLOW命令帮助：FOLLOW 帐号。有问题吗？
_STR_;

		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$serverAddress  = $robotMsg->GetServerAddress();
		$type 		= $robotMsg->GetType();	

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($address_device_db_row['idUser']) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id	= $address_device_db_row['idUser'];


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) ) {
			/*
			 * seek 2007/07/25
			 * 当用户发送follow命令到特服号码，直接follow
			 */
			$reply_to = JWSns::GetReplyTo($address_user_id, $serverAddress, $type);
			if( !empty($reply_to) && $reply_to['smssuffix'] ){
				$followe = $reply_to['user_id'];
			}else{
				return JWRobotLogic::ReplyMsg($robotMsg, $help);
			}
		}else{
			$followe = $matches[1];
		}

		/*
		 *	获取被订阅者的用户信息
		 *	TODO: 权限检查
		 */
		$followe_user_db_row = JWUser::GetUserInfo( $followe );

		if ( empty($followe_user_db_row) )
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！抱歉，您订阅的"
											. "${followe}我不认识，"
											. "请您确认输入了正确的叽歪帐号。了解更多？发送 HELP。"
										);
	
		if ( ! JWFriend::IsFriend($address_user_id, $followe_user_db_row['idUser']) )
			JWFriend::Create($address_user_id, $followe_user_db_row['idUser']);
/*
7/24/07 zixia: 不用添加好友即可直接 follow.
				return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！抱歉，您只可以订阅好友的更新。通过ADD ${followe}命令添加好友。"
											. "了解更多？发送 HELP。"
										);
*/
			

		JWSns::CreateFollowers($followe_user_db_row['idUser'], array($address_user_id));


		$body = <<<_STR_
每当$followe_user_db_row[nameFull]更新，您都会收到消息。如果要撤销，请发送LEAVE $followe_user_db_row[nameScreen]。发送HELP了解更多。
_STR_;

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Leave($robotMsg)
	{
		$help = <<<_STR_
LEAVE命令帮助：LEAVE 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
LEAVE命令帮助：LEAVE 帐号。
_STR_;


		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$address_device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($address_device_db_row['idUser']) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id	= $address_device_db_row['idUser'];

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);


		$followe = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$followe_user_db_row = JWUser::GetUserInfo( $followe );

		if ( empty($followe_user_db_row) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		JWSns::DestroyFollowers($followe_user_db_row['idUser'], array($address_user_id));


		$body = <<<_STR_
您已退定了$followe_user_db_row[nameFull]。在http://JiWai.de/$followe_user_db_row[nameScreen]/页面上点击订阅或发送FOLLOW $followe_user_db_row[nameScreen]可恢复订阅。
_STR_;
		$body = <<<_STR_
您已退定了$followe_user_db_row[nameFull]。
_STR_;

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
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
		$help = <<<_STR_
ADD命令帮助：ADD 帐号。
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

		if ( empty($address_user_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

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
				return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！抱歉，您添加的"
												. "${user_input_invitee_address}我不认识，"
#. "请您输入叽歪帐号或手机号、邮件地址。了解更多？发送 HELP。"
												. "请您输入帐号或手机号、邮件地址。了解更多？发送 HELP。"
											);
			}

			$friend_user_id	= $friend_user_db_row['idUser'];

			if ( JWFriend::IsFriend($address_user_id, $friend_user_id) )
			{
				$msg = "好消息！${invitee_address}已经是您的好友了，不用重复添加啦。";
			}
			else
			{
				$is_protected = JWUser::IsProtected		( $friend_user_id );

				if ( $is_protected )
				{
					if ( JWFriendRequest::IsExist($address_user_id, $friend_user_id) )
					{
						$msg = <<<_STR_
您向${invitee_address}发送的添加好友请求，他还没有回应，再等等看吧。
_STR_;
					}
					else if ( JWSns::CreateFriendRequest($address_user_id, $friend_user_id) )
					{
						$msg = <<<_STR_
搞定了！我们已经帮您向${invitee_address}发送了好友添加请求，希望能够很快得到${invitee_address}的回应。
_STR_;
					}
					else
					{
						$msg = <<<_STR_
哎呀！由于系统故障，发送好友请求失败了……请稍后再试吧。
_STR_;
					}
				}
				else	// not protected
				{
					JWSns::CreateFriends	( $address_user_id	,array($friend_user_id) );
					JWSns::CreateFollowers	( $friend_user_id	,array($address_user_id) );

					$msg = <<<_STR_
搞定了！已经将${invitee_address}添加为您的好友啦！
_STR_;
				}
			}

			/*
			 *	已经添加用户完毕，返回
			 */
			return JWRobotLogic::ReplyMsg($robotMsg, $msg);

		}


		if ( ! JWDevice::IsValid($invitee_address,$invitee_type) )
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
			$body = <<<_STR_
${invitee_address}已经注册！我们已经帮您发送了好友添加请求。
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
			$invite_msg['email'] = <<<_INVITATION_
$address_user_row[nameFull]（$address_user_row[nameScreen]）邀请您来使用我们的服务！
_INVITATION_;

			$invite_msg['im'] = $invite_msg['email'] . <<<_INVITATION_
请回复您的英文名字或拼音，这样我们可以帮助您完成注册。
_INVITATION_;
			$invite_msg['sms'] = $invite_msg['im'] . "（本短信服务免费）";

			JWSns::Invite($address_user_id, $invitee_address, $invitee_type, $invite_msg);

			$body = <<<_STR_
搞定了！我们已经帮您发出了邀请！期待很快能得到您朋友的回应。
_STR_;
		}


        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Delete($robotMsg)
	{
		$help = <<<_STR_
DELETE命令帮助：DELETE 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
DELETE命令帮助：DELETE 帐号。
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
		$param_body = self::ConvertCorner( $param_body );

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$friend_name = $matches[1];

		/*
		 *	获取被删除者的用户信息
		 */
		$friend_user_row = JWUser::GetUserInfo( $friend_name );

		if ( empty($friend_user_row) )
			return JWRobotLogic::ReplyMsg($robotMsg, "没有找到 $friend_name 这个用户。");


		JWSns::DestroyFriends	($address_user_id			,array($friend_user_row['idUser']));
		JWSns::DestroyFollowers	($friend_user_row['idUser']	,array($address_user_id));


		$body = <<<_STR_
搞定了！$friend_user_row[nameFull]($friend_user_row[nameScreen])已经不再是您的好友了。
_STR_;

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Get($robotMsg)
	{
		$help = <<<_STR_
使用方法：GET 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
使用方法：GET 帐号。
_STR_;

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

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

		if ( empty($status_ids['status_ids']) )
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

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Nudge($robotMsg)
	{
		$help = <<<_STR_
使用方法：NAO 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
使用方法：NAO 帐号。
_STR_;

		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	


		$address_device_id		= JWDevice::GetDeviceIdByAddress( array('address'=>$address,'type'=>$type) );
		$address_device_db_row 	= JWDevice::GetDeviceDbRowById	($address_device_id);

		// 用户没有注册过
		if ( empty($address_device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id		= $address_device_db_row['idUser'];



		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

		if ( ! preg_match('/^\w+\s+(\w+)\s*$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);


		$friend_name 		= $matches[1];
		$friend_user_db_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_db_row) )
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！没有找到 $friend_name 这个用户！");

		$friend_user_id		= $friend_user_db_row['idUser'];

		$send_via_device	= JWUser::GetSendViaDeviceByUserId($friend_user_id);

		// TODO 要考虑判断用户的 device 是否已经通过验证激活
		if ( 'web'==$send_via_device )
			return JWRobotLogic::ReplyMsg($robotMsg, "$friend_user_db_row[nameFull]已经关闭了通知，不想被挠挠。。。要不您稍后再试吧？");

		if ( ! JWFriend::IsFriend($friend_user_db_row['idUser'], $address_user_id) )
			return JWRobotLogic::ReplyMsg($robotMsg, "对不起，您还不是$friend_user_db_row[nameFull]的好友呢，"
											."不能随便挠挠，呵呵。等您被加为好友再挠吧!");

		$address_user_db_row	= JWUser::GetUserDbRowById($address_user_id);


		$nudge_message = <<<_NUDGE_
$address_user_db_row[nameScreen]挠挠了您一下，提醒您更新JiWai！回复本消息既可更新您的JiWai。
_NUDGE_;
		$nudge_message = <<<_NUDGE_
$address_user_db_row[nameScreen]挠挠了您一下，提醒您更新！回复本消息既可更新。
_NUDGE_;

		JWNudge::NudgeUserIds(array($friend_user_db_row['idUser']), $nudge_message, 'nudge');

		$body = <<<_STR_
我们已经帮您挠挠了$friend_user_db_row[nameScreen]一下！期待很快能得到您朋友的回应。
_STR_;
		$body = <<<_STR_
我们已经帮您挠挠了$friend_user_db_row[nameScreen]一下！期待很快能得到您朋友的回应。
_STR_;

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}



	/*
	 *
	 */
	static function	Lingo_Whois($robotMsg)
	{
		$help = <<<_STR_
使用方法：WHOIS 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
使用方法：WHOIS 帐号。
_STR_;

		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

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
			$body .= "，自述：$friend_user_row[bio]";

		if ( !empty($friend_user_row['location']) )
			$body .= "，位置：$friend_user_row[location]";

		if ( !empty($friend_user_row['url']) )
			$body .= "，网站：$friend_user_row[url]";


        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Accept($robotMsg)
	{
		$help = <<<_STR_
ACCEPT命令帮助：ACCEPT 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
ACCEPT命令帮助：ACCEPT 帐号。
_STR_;


		/*
	 	 *	解析命令参数
	 	 */
		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

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
哎呀！没有找到这个用户($inviter_name)，是不是改名了？。发送HELP了解更多。
_STR_;
		}

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_Deny($robotMsg)
	{
		$help = <<<_HELP_
DENY命令帮助：DENY 帐号。有问题吗？上 http://JiWai.de/ 看看吧。
_HELP_;
		$help = <<<_HELP_
DENY命令帮助：DENY 帐号。
_HELP_;

		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

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
			$body = <<<_STR_
哎呀！没有找到邀请您的朋友${friend_name}。
_STR_;
		}
		else
		{
			$body = <<<_STR_
搞定了！您没有接受$friend_db_row[nameFull]($friend_db_row[nameScreen])的邀请。发送GET $friend_db_row[nameScreen]获取$friend_db_row[nameFull]的最新更新（本短信服务免费）
_STR_;
		}

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}


	/*
	 *
	 */
	static function	Lingo_D($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType() ;

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id	= $device_db_row['idUser'];

		$help = <<<_STR_
D命令帮助：D 帐号 您想说的悄悄话。有问题吗？上 http://JiWai.de/ 看看吧。
_STR_;
		$help = <<<_STR_
D命令帮助：D 帐号 您想说的悄悄话。
_STR_;

		$param_body = $robotMsg->GetBody();
		$param_body = self::ConvertCorner( $param_body );

		if ( ! preg_match('/^\w+\s+(\w+)\s+(.+)$/i',$param_body,$matches) )
			return JWRobotLogic::ReplyMsg($robotMsg, $help);

		$friend_name 	= $matches[1];
		$message_text	= $matches[2];

		$friend_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_row) )
		{
			return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！没有找到 ${friend_name} 这个用户。了解更多？发送 HELP。");
		}

		$friend_id = $friend_row['idUser'];

		if ( !JWFriend::IsFriend($friend_id, $address_user_id) )
		{
			if ( JWFriend::IsFriend($address_user_id, $friend_id) )
			{
				return JWRobotLogic::ReplyMsg($robotMsg, "哎呀！您现在还不在${friend_name}的好友列表中，无法悄悄话他(她)。:-(");
			}
			else
			{
				return JWRobotLogic::ReplyMsg($robotMsg, "您还不在${friend_name}的好友列表中，无法悄悄话。"
									."您可以发送ADD ${friend_name}将${friend_name}添加为好友，然后期待也被对方添加为好友啦！");
			}
		}
		

		JWSns::CreateMessage($address_user_id, $friend_id, $message_text, $type);

		return null;
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
		$is_web_user		= JWUser::IsWebUser($address_user_row['idUser']);
	
		if ( $is_web_user )
		{
			$body = <<<_STR_
您是$address_user_row[nameFull] ($address_user_row[nameScreen])，叽歪档案位于：http://jiwai.de/$address_user_row[nameScreen]/ 。
_STR_;
			$body = <<<_STR_
您是$address_user_row[nameFull] ($address_user_row[nameScreen]) 。
_STR_;
		}
		else
		{
			$body = <<<_STR_
您是$address_user_row[nameScreen]，叽歪档案位于：http://jiwai.de/$address_user_row[nameScreen]/ 。设置密码请来这里：http://jiwai.de/wo/account/complete
_STR_;
			$body = <<<_STR_
您是$address_user_row[nameScreen]，设置密码请来这里：http://jiwai.de/wo/account/complete
_STR_;
		}

        return JWRobotLogic::ReplyMsg($robotMsg, $body);
	}
	
	/**
	 * 将字符串转化为半角，从而支持半角指令
	 * @param string $string , 
	 * @return string
	 */
	static function ConvertCorner($string){
		$corner = array(
			'１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5',
			'６' => '6', '７' => '7', '８' => '8', '９' => '9', '０' => '0',
			'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e',
			'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j',
			'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o',
			'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't',
			'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y',
			'ｚ' => 'z', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D',
			'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I',
			'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N',
			'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S',
			'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X',
			'Ｙ' => 'Y', 'Ｚ' => 'Z', '＠' => '@', '　' => ' '
	    	);
		return str_replace(array_keys($corner), array_values($corner), $string);
	}
}
?>
