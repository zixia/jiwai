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
			'REPLY_HELP_SUC'	=> '告诉我们这一刻您在做什么，我们会让您的朋友们知道！想要更多帮助，回复TIPS。',
			'REPLY_TIPS_SUC'	=> '命令：ON、OFF、WHOIS帐号、NN帐号、FOLLOW帐号、LEAVE帐号、ADD帐号。',

			'REPLY_ADD_HELP' => 'ADD命令帮助：ADD 帐号。',
			'REPLY_ADD_WAIT_EXISTS' => '你向 {0} 发送的关注请求，他还没有回应，再等等看吧。',
			'REPLY_ADD_WAIT_SUC' => '搞定了！我们已经帮你向 {0} 发送了关注请求，希望能够很快得到{0}的回应。',
			'REPLY_ADD_NOUSER' => '哎呀！抱歉，你添加的 {0} 我不认识，请你输入帐号或手机号、邮件地址。了解更多？发送 HELP。',
			'REPLY_ADD_500' => '哎呀！由于系统故障，发送关注请求失败了……请稍后再试吧。',
			'REPLY_ADD_NOADDRESS' => '哎呀！抱歉，我太笨了。你添加的 {0} 我不认识，请你输入手机号码或邮件地址。了解更多？发送 HELP。',
			'REPLY_ADD_REQUEST' => '{0} 已经注册！我们已经帮你发送了关注请求。',
			'REPLY_ADD_REQUEST_INVITE' => '搞定了！我们已经帮你发出了邀请！期待很快能得到你朋友的回应。',
			'REPLY_ADD_SELF' => '{0}，你好，{1} 是你自己的地址，邀请自己没有意义，邀请别人才有意义，活着就要做有意义的事。',

			'REPLY_DENY_SUCC' => '搞定了！你没有接受 {0} 的邀请。发送GET {0} 获取 {0} 的最新更新。',
			'REPLY_DENY_NOINVITE' => '哎呀！{0} 并没有邀请你。发送HELP了解更多。',
			'REPLY_DENY_HELP' => 'DENY命令帮助：DENY 帐号。',
			'REPLY_DENY_NOUSER' => '哎呀！没有找到邀请你的朋友 {0}。',

			'REPLY_DELETE_SUC' => '搞定了！你已经不再关注 {0} 了。',
			'REPLY_DELETE_HELP' => 'DELETE命令帮助：DELETE 帐号。',
			'REPLY_DELETE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_GET_SUC' => '{0}: {1}',
			'REPLY_GET_HELP' => 'GET命令帮助：GET 帐号。',
			'REPLY_GET_NOSTATUS' => '还没有更新过。',
			'REPLY_GET_NOPERM' => '哎呀！{0} 设置了只对好友开发叽歪更新，而你还不是 {0} 的好友',

			'REPLY_D_HELP' => 'D命令帮助：D 帐号 你想说的悄悄话。',
			'REPLY_D_SUC'	=> '悄悄话已经发送给 {0} ，也许 {0} 会马上回复你的哦。',
			'REPLY_D_NOPERM_BIO' => '你还不在 {0} 的好友列表中，无法悄悄话。你可以发送ADD {0} 将 {0} 添加为好友， 然后期待也被对方添加为好友啦！',
			'REPLY_D_NOPERM' => '你还不在 {0} 的好友列表中，无法悄悄话。',
			'REPLY_D_NOUSER' => '哎呀！没有找到 {0} 这个用户。了解更多？发送 HELP。',

			'REPLY_ON_SUC' => '搞定了！你在做什么呢？任何时候发送 OFF 都可以随时关掉通知。',
			'REPLY_ON_ERR' => '哎呀！抱歉，打开通知失败，我们会尽快解决。',
			'REPLY_ON_SUC_USER' => '搞定了！设置接受 {0} 的更新通知成功，发送 OFF {0} 关闭更新通知。',
			'REPLY_ON_ERR_USER' => '哎呀！抱歉，设置接收 {0} 的更新通知失败，我们会尽快解决。',

			'REPLY_OFF_SUC' => '通知消息已关闭。发送 ON 可开启。',
			'REPLY_OFF_SUC_USER' => '搞定了！设置不接受 {0} 的更新通知成功，发送 ON {0} 可启更新通知。',

			'REPLY_ON_SUC_MUL' => '搞定了！设置接受 {0} 的更新通知成功。',
			'REPLY_OFF_SUC_MUL' => '搞定了！设置不接受 {0} 的更新通知成功。',

			'REPLY_FOLLOW_HELP' => 'FOLLOW命令帮助：FOLLOW 帐号。',
			'REPLY_FOLLOW_SUC' => '搞定了！已经成功关注 {0} 了！',
			'REPLY_FOLLOW_NOUSER' => '哎呀！抱歉，你关注的 {0} 我不认识，请你确认输入了正确的叽歪帐号。',
			'REPLY_FOLLOW_EXISTS' => '好消息！你已经关注 {0} 了。',

			'REPLY_LEAVE_HELP' => 'LEAVE命令帮助：LEAVE 帐号。',
			'REPLY_LEAVE_SUC' => '你已经取消对 {0} 的关注。',
			'REPLY_LEAVE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_WHOIS_HELP' => '使用方法：WHOIS 帐号。',
			'REPLY_WHOIS_SUC' => '{0}',
			'REPLY_WHOIS_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_NUDGE_HELP' => '使用方法：NAO 帐号。',
			'REPLY_NUDGE_SELF' => '后背痒痒？自己挠是够不着的，我想你得等着别人来挠你了。',
			'REPLY_NUDGE_DENY' => '{0} 已经关闭了通知，不想被挠挠。。。要不你稍后再试吧？',
			'REPLY_NUDGE_SUC' => '我们已经帮你挠挠了 {0} 一下！期待很快能得到你朋友的回应。',
			'REPLY_NUDGE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',
			'REPLY_NUDGE_NOPERM' => '对不起，你暂时不能对 {0} 执行挠挠操作。',

			'REPLY_REG_HELP' => '使用方法：REG 账户 [昵称]，[]内为可选。',
			'REPLY_REG_HELP_GM' => '使用方法：GM 账户 [昵称]，[]内为可选。',
			'REPLY_REG_SUC_NICK' => '好消息，你的昵称修改为 {0} 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SUC_SCREEN' => '好消息，你的帐户名修改为 {0} 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SUC_ALL' => '好消息，修改账户及昵称为 {1} ({0}) 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SAME' => '哇，你本来就叫 {0}，压根不需要改哦。',
			'REPLY_REG_INVALID_NAME' => '你要修改的用户名 {0} 不合法，请重新选择。',
			'REPLY_REG_HOT' => '哇，你要注册的账户名 {0} 太热，请重新选择一个吧。',
			'REPLY_GM_HOT' => '哇，你要修改的账户名 {0} 太热，请重新选择一个吧。',
			'REPLY_REG_500'  => '哎呀！由于系统故障，你的请求失败了…… 请稍后再试吧。',
			'REPLY_REG_REPLY_SUC' => '你好，你已经完成了注册，你的用户名是 {0}',
			'REPLY_CREATE_USER_FIRST' => '恭喜您成功获得用户名 {0} ！改名请回复 GM+空格+新用户名',

			'REPLY_NOREG_TIPS' => '你还没有注册，请回复你想要的账户名。',

			'REPLY_WHOAMI' => '你是 {0}',
			'REPLY_WHOAMI_WEB' => '你是 {0}，你的叽歪档案位于：http://JiWai.de/{0}',
			'REPLY_WHOAMI_IM' => '你是 {0}，设置密码请来这里：http://JiWai.de/wo/account/complete',

			'REPLY_ACCEPT_SUC' => '搞定了！你已经开始关注 {0} ！回复 GET {0} 查看最新更新。',
			'REPLY_ACCEPT_HELP' => 'ACCEPT命令帮助：ACCEPT 帐号。',
			'REPLY_ACCEPT_NOUSER' => '哎呀！没有找到 {0} 这个用户。',
			'REPLY_ACCEPT_INVITE' => '哎呀！{0} 没有邀请过你。请回复你希望使用的用户名。',
			'REPLY_ACCEPT_INVITE_SUC' => '搞定了！你已经接受了 {0} 的邀请。请回复你希望使用的用户名。',
			'REPLY_ACCEPT_INVITE_NOUSER' => '哎呀！没有找到这个用户 {0}，是不是改名了？。发送HELP了解更多。',
			'REPLY_ACCEPT_SUC_REQUEST' => '搞定了！你已经接受了 {0} 关注你。',
			'REPLY_ACCEPT_SUC_NOREQUEST' => '哎呀！虽然 {0} 并没有邀请过你，但你还是关注 {0} 了。',
			'REPLY_ACCEPT_500' => '哎呀！由于系统故障，你的接受操作失败了…… 请稍后再试吧。',

			'REPLY_VERIFY_SUC' => '搞定了！你已经通过了验证。回复本消息即可进行更新，耶！',
			'REPLY_VERIFY_FAIL' => '哎呀！由于你输入的验证码 "{0}" 不正确，本次验证未能成功，请你查证后再重试一下吧。',

			/**
			 * 一般回复
			 */
			'REPLY_500'  => '哇，真可怕！由于系统故障，你的请求失败了…… 请稍后再试吧。',
			'REPLY_NOUSER' => '哎呀！没有找到用户 {0}',

			//Out
			'OUT_NUDGE' => '{0} 挠挠了你一下，提醒你更新！回复本消息既可更新。',

			'OUT_ADD_EMAIL' => '{0}({1}) 邀请你来使用我们的服务！',
			'OUT_ADD_IM' => '{0}({1}) 邀请你来使用我们的服务！请回复你的英文名字或拼音，这样我们可以帮助你完成注册。',
			'OUT_ADD_SMS' => '{0}({1}) 邀请你来使用我们的服务！请回复你的英文名字或拼音，这样我们可以帮助你完成注册。(本短信服务免费)',

			'OUT_FOLLOW' => '好消息！{0}( http://JiWai.de/{1}/ ) 关注了你。',

			'REPLY_MMS_NOPERM' => '{0}，你好。由于{1}设置了私密，而你还没关注 {1}，不能下载彩信。',
			'REPLY_MMS_NOMMS' => '哇，没搞错吧？没有找到你要的彩信信息。',
			'REPLY_MMS_NOSMS' => '{0}，你没有绑定手机或没有通过手机验证，不能下载彩信。',
			'REPLY_MMS_SUC_IM' => '{0}，{1} 上传的彩信[{2}]即将发往你绑定的手机，请注意查收。',
			'REPLY_MMS_HELP' => '只能使用手机回复 DM 到给定特服号获取彩信。',
			'REPLY_MMS_ILL' => '哇，真可怕！我们不知道你究竟想要作什么！',

			'REPLY_0000_HELP' => '发送 OFF 取消手机信息提示。',

			//Track
			'REPLY_TRACK_SHOW' => '你关注的关键词有：{0}',
			'REPLY_TRACK_HELP' => '你还没有关注过任何关键词，请输入 TRACK 要关注的关键词',
			'REPLY_TRACK_SUC' => '你将收到匹配 {0} 的更新，取消关注请输入 UNTRACK {0}',
			'REPLY_UNTRACK_SUC' => '你将不再收到匹配 {0} 的更新',
			'REPLY_UNTRACK_HELP' => 'UNTRACK用法：UNTRACK 要取消关注的关键词',

			//Block
			'REPLY_UNBLOCK_HELP' => 'UNBLOCK命令用法：UNBLOCK 用户名',
			'REPLY_BLOCK_HELP' => '你还没有阻止过任何人，阻止某人请使用 BLOCK 用户名',
			'REPLY_BLOCK_LIST' => '被你阻止的用户有：{0} ，取消阻止请输入 UNBLOCK 用户名',
			'REPLY_BLOCK_SUC' => '你阻止用户 {0} 成功，将不再会受其侵扰。',
			'REPLY_UNBLOCK_SUC' => '你取消了阻止用户 {0}',

			//PASS
			'REPLY_PASS_SUC' => '你好，{0} 你的新密码是：{1} 到 http://JiWai.de/wo/login 上登录去吧',
			'REPLY_PASS_HELP' => '设定新密码 请使用 PASS+空格+新密码',

			//Merge
			'REPLY_MERGE_OWN' => '你好，当前设备绑定的用户正是 {0} ，无需合并',
			'REPLY_MERGE_WEBUSER' => '你好，你当前设备用户已经在 WEB 上登录并设定了密码，请联系管理员吧',
			'REPLY_MERGE_SUC' => '你好，当前设备 {0}://{1} 已转移绑定到用户 {2} ，常回来叽歪呀～',
			'REPLY_MERGE_MULTI' => '你好，你的当前用户，已经绑定了多个设备，还是继续使用吧',
			'REPLY_MERGE_HAVE' => '你好，{0} 已经绑定了 {1}://{2} ，删除绑定后才能合并过来',
			'REPLY_MERGE_ERR' => '你提供的合并用户名 {0} 和你提供的密码不匹配，无法完成操作',
			'REPLY_MERGE_WEBREQ' => '你好，合并帐户的请求只能从 (MSN/QQ/Skype/GTalk/Yahoo!/SMS) 端发起',
			'REPLY_MERGE_TIPS' => '{0}用法：{0}+空格+web账户名+空格+web账户密码',
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

	static function GetReplyString( $robotMsg, $shortcut, $value=array() )
	{

		$serverAddress = $robotMsg->GetServerAddress();
		$type = $robotMsg->GetType();
		$address = $robotMsg->GetAddress();

		$idUserConference = JWRobotLingoBase::GetLingoUser( $serverAddress, $address, $type );

		$replyMap = self::GetReplyMap( $idUserConference );
		$shortcut = strtoupper( $shortcut );

		return self::FetchReplyString($replyMap, $shortcut, $value );
	}

	static function FetchReplyString($replyMap, $shortcut, $value=array())
	{
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
				$map['REPLY_WHOAMI_IM'] = '您的昵称是{0}，更改昵称请回复：GM + 空格 + 昵称。';
				$map['REPLY_REG_MSG'] = '欢迎您参与《午夜心语》节目，本短信服务不收任何信息费，正常通信费除外，请把你的昵称作为短信内容直接回复。';
				$map['REPLY_REG_SUC'] = '{0}，《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。您可回复"GM + 空格 + 昵称"获得个性昵称。';
				$map['REPLY_UPDATESTATUS'] = '{0}, 《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。';
				$map['REPLY_NAMEFULL'] = '午夜过客';
			}
			break;
			case 28006:
			{
				$map['REPLY_WHOAMI_IM'] = '您的昵称是{0}，更改昵称请回复：GM + 空格 + 昵称。';
				$map['REPLY_REG_MSG'] = '欢迎您参与《亲子港湾》节目，本短信服务不收任何信息费，正常通信费除外，请把你的昵称作为短信内容直接回复。';
				$map['REPLY_REG_SUC'] = '感谢您对吉林教育电视台《亲子港湾》栏目的支持，您发送的短信即将播出，敬请关注。输入 A 回复此短信即可免费成为本栏目家长俱乐部的会员。';
				$map['REPLY_FOLLOW_SUC'] = '恭喜您已成为《亲子港湾》家长俱乐部的会员，您在家庭教育中的难题或困惑，我们有专家为您解答，您有成功的成长成才经验，我们期望与您分享。';
				$map['REPLY_UPDATESTATUS'] = '感谢您对吉林教育电视台《亲子港湾》栏目的支持，您发送的短信即将播出，敬请关注。输入 A 回复此短信即可免费成为本栏目家长俱乐部的会员。';
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
