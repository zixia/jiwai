<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Reply Class
 */
class JWRobotLingoReply {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingoReply
	 */
	static private $msInstance;

	static private $msReplyMap = array(
			//Reply
			'REPLY_HELP'	=> '告诉我们这一刻您在做什么，我们会让您的朋友们知道！想要更多帮助，回复TIPS。',
			'REPLY_TIPS'	=> '命令：ON、OFF、WHOIS帐号、NN帐号、FOLLOW帐号、LEAVE帐号、ADD帐号。',

			'REPLY_ADD_SUC' => '搞定了！已经将{0}添加为你的好友啦！',
			'REPLY_ADD_HELP' => 'ADD命令帮助：ADD 帐号。',
			'REPLY_ADD_EXISTS' => '好消息！{0}已经是你的好友了，不用重复添加啦。',
			'REPLY_ADD_WAIT_EXISTS' => '你向{0}发送的添加好友请求，他还没有回应，再等等看吧。',
			'REPLY_ADD_WAIT_SUC' => '搞定了！我们已经帮你向{0}发送了好友添加请求，希望能够很快得到{0}的回应。',
			'REPLY_ADD_NOUSER' => '哎呀！抱歉，你添加的 {0} 我不认识，请你输入帐号或手机号、邮件地址。了解更多？发送 HELP。',
			'REPLY_ADD_500' => '哎呀！由于系统故障，发送好友请求失败了……请稍后再试吧。',
			'REPLY_ADD_NOADDRESS' => '哎呀！抱歉，我太笨了。你添加的 {0} 我不认识，请你输入手机号码或邮件地址。了解更多？发送 HELP。',
			'REPLY_ADD_REQUEST' => '{0}已经注册！我们已经帮你发送了好友添加请求。',
			'REPLY_ADD_REQUEST_INVITE' => '搞定了！我们已经帮你发出了邀请！期待很快能得到你朋友的回应。',

			'REPLY_DENY_SUCC' => '搞定了！你没有接受{0}({1})的邀请。发送GET {1}获取 {0} 的最新更新。',
			'REPLY_DENY_NOINVITE' => '哎呀！{0} 并没有邀请你。发送HELP了解更多。',
			'REPLY_DENY_HELP' => 'DENY命令帮助：DENY 帐号。',
			'REPLY_DENY_NOUSER' => '哎呀！没有找到邀请你的朋友 {0}。',

			'REPLY_DELETE_SUC' => '搞定了！{0}({1})已经不再是你的好友了。',
			'REPLY_DELETE_HELP' => 'DELETE命令帮助：DELETE 帐号。',
			'REPLY_DELETE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_GET_SUC' => '{0}: {1}',
			'REPLY_GET_HELP' => 'DELETE命令帮助：DELETE 帐号。',
			'REPLY_GET_NOSTATUS' => '还没有更新过。',

			'REPLY_D_HELP' => 'D命令帮助：D 帐号 你想说的悄悄话。',
			'REPLY_D_SUC'	=> '',
			'REPLY_D_NOPERM_BIO' => '你还不在{0}的好友列表中，无法悄悄话。你可以发送ADD {0}将{0}添加为好友， 然后期待也被对方添加为好友啦！',
			'REPLY_D_NOPERM' => '你还不在{0}的好友列表中，无法悄悄话。',
			'REPLY_D_NOUSER' => '哎呀！没有找到 {0} 这个用户。了解更多？发送 HELP。',

			'REPLY_ON_SUC' => '搞定了！你在做什么呢？任何时候发送OFF都可以随时关掉通知。',
			'REPLY_OFF_SUC' => '通知消息已关闭。发送ON可开启。',

			'REPLY_FOLLOW_HELP' => 'FOLLOW命令帮助：FOLLOW 帐号。',
			'REPLY_FOLLOW_SUC' => '每当{0}更新，你都会收到消息。如果要撤销，请发送LEAVE {1}。发送HELP了解更多。',
			'REPLY_FOLLOW_NOUSER' => '哎呀！抱歉，你订阅的 {0} 我不认识，请你确认输入了正确的叽歪帐号。了解更多？发送 HELP。',

			'REPLY_LEAVE_HELP' => 'LEAVE命令帮助：LEAVE 帐号。',
			'REPLY_LEAVE_SUC' => '你已退定了{0}。',
			'REPLY_LEAVE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_WHOIS_HELP' => '使用方法：WHOIS 帐号。',
			'REPLY_WHOIS_SUC' => '{0}',
			'REPLY_WHOIS_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_NUDGE_HELP' => '使用方法：NAO 帐号。',
			'REPLY_NUDGE_DENY' => '{0}已经关闭了通知，不想被挠挠。。。要不你稍后再试吧？',
			'REPLY_NUDGE_SUC' => '我们已经帮你挠挠了{0}一下！期待很快能得到你朋友的回应。',
			'REPLY_NUDGE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',
			'REPLY_NUDGE_NOPERM' => '对不起，你还不是{0}的好友呢，不能随便挠挠。等你被加为好友后，再挠吧！',

			'REPLY_REG_HLEP' => '使用方法：REG 账户 [昵称]，[]内为可选。',
			'REPLY_REG_HLEP_GM' => '使用方法：GM 账户 [昵称]，[]内为可选。',
			'REPLY_REG_SUC_NICK' => '好消息，你的昵称修改为 {0} 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SUC_SCREEN' => '好消息，你的帐户名修改为 {0} 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SUC_ALL' => '好消息，修改账户及昵称为 {1} ({0}) 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SAME' => '哇，你本来就叫 {0}，压根不需要改哦。',
			'REPLY_REG_HOT' => '哇，你要修改的账户名 {0} 太热，请重新选择一个吧。',
			'REPLY_REG_500'  => '哎呀！由于系统故障，你的请求失败了…… 请稍后再试吧。',

			'REPLY_NOREG_TIPS' => '你还没有注册，请回复你想要的账户名。',

			'REPLY_WHOAMI' => '你是{0}({1})。',
			'REPLY_WHOAMI_WEB' => '你是{0}({1})，你的叽歪档案位于：http://JiWai.de/{1}',
			'REPLY_WHOAMI_IM' => '你是{0}({1})，设置密码请来这里：http://JiWai.de/wo/account/complete',

			'REPLY_ACCEPT_SUC' => '搞定了！你和{0}({1})已经成为好友！回复GET {1}查看最新更新。',
			'REPLY_ACCEPT_HELP' => 'ACCEPT命令帮助：ACCEPT 帐号。',
			'REPLY_ACCEPT_NOUSER' => '哎呀！没有找到 {0} 这个用户。',
			'REPLY_ACCEPT_INVITE' => '哎呀！{0}没有邀请过你。请回复你希望使用的用户名。',
			'REPLY_ACCEPT_INVITE_SUC' => '搞定了！你已经接受了{0}的邀请。请回复你希望使用的用户名。',
			'REPLY_ACCEPT_INVITE_NOUSER' => '哎呀！没有找到这个用户 {0}，是不是改名了？。发送HELP了解更多。',

			'REPLY_VERIFY_SUC' => '搞定了！你已经通过了验证。回复本消息即可进行更新，耶！',
			'REPLY_VERIFY_FAIL' => '哎呀！由于你输入的验证码"{0}"不正确，本次验证未能成功，请你查证后再重试一下吧。',

			'REPLY_500'  => '哇，真可怕！由于系统故障，你的请求失败了…… 请稍后再试吧。',
			'REPLY_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			//Out
			'OUT_NUDGE' => '{0}挠挠了你一下，提醒你更新！回复本消息既可更新。',

			'OUT_ADD_EMAIL' => '{0}({1})邀请你来使用我们的服务！',
			'OUT_ADD_IM' => '{0}({1})邀请你来使用我们的服务！请回复你的英文名字或拼音，这样我们可以帮助你完成注册。',
			'OUT_ADD_SMS' => '{0}({1})邀请你来使用我们的服务！请回复你的英文名字或拼音，这样我们可以帮助你完成注册。(本短信服务免费)',
			

		);

	/**
	 * Instance of this singleton class
	 *
	 * @return JWRobotLingoReply
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

	static function GetReplyString( $robotMsg, $shortcut, $value=array() ){

		$serverAddress = $robotMsg->GetServerAddress();
		$type = $robotMsg->GetType();

		$idUserConference = JWRobotLingoBase::GetLingoUser( $serverAddress, $type );

		$replyMap = self::GetReplyMap( $idUserConference );

		$shortcut = strtoupper( $shortcut );

		if( isset( $replyMap[ $shortcut ] ) ){

			$replyString = $replyMap[ $shortcut ];

			$va = array_values( $value );
			foreach( $va as $k=>$v ) {
				$replyString = str_replace( '{'.$k.'}', $v, $replyString ); 
			}

			return $replyString;
		}

		return null;
	}

	static function GetReplyMap( $idUserConference ) {

		$map = self::$msReplyMap;

		switch( $idUserConference ) {
			case 99:
			{
				$map['REPLY_WHOAMI_IM'] = '你是{0}，改名请用：GM 新昵称。';
				$map['REPLY_REG_MSG'] = '欢迎您参与《午夜心语》节目，本短信服务不收任何信息费，正常通信费除外，请把你的昵称作为短信内容直接回复。';
				$map['REPLY_REG_SUC'] = '{0}，《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。您可发送"GM + 空格 + 昵称"获得个性昵称。';
				$map['REPLY_UPDATESTATUS'] = '{0}, 《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。';
				$map['REPLY_NAMEFULL'] = '午夜过客';
			}
			break;
			case 28006:
			{
				$map['REPLY_WHOAMI_IM'] = '你是{0}，改名请用：GM 新昵称。';
				$map['REPLY_REG_MSG'] = '欢迎您参与《亲子港湾》节目，本短信服务不收任何信息费，正常通信费除外，请把你的昵称作为短信内容直接回复。';
				$map['REPLY_REG_SUC'] = '{0}，《亲子港湾》谢谢您的参与！您发送的短信即将播出，请密切关注。您可发送"GM + 空格 + 昵称"获得个性昵称。';
				$map['REPLY_FOLLOW_SUC'] = '恭喜您已成为《亲子港湾》家长俱乐部的会员，您在家庭教育中的难题或困惑，我们有专家为您解答，您有成功的成长成才经验，我们期望与您分享。';
				$map['REPLY_UPDATESTATUS'] = '感谢您的参与：我们会定期邀请知名家教专家与您一起探讨家教问题，具体信息请留意栏目信息或拨打栏目热线0431-5331130。';
			}
			break;
			default:
			{
			}
			break;
		}

		return $map;
	}

}
?>
