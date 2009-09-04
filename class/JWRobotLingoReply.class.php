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
			'REPLY_HELP_SUC'	=> '改名：发送gm+空格+新名字，例如“gm girl”；发悄悄话给别人：发送D+空格+别人的名字+空格+悄悄话；关闭接收消息：发送guan',
			'REPLY_TIPS_SUC'	=> 'help、t、a、on、off、get、dict、d、dd、whoami、whois、nudge、block、merge、add、track ',

			'REPLY_ADD_HELP' => '邀请好友发送：add+空格+手机号或电子邮箱地址，例如“add 138XXXXX973”“add name@email.com” ',
			'REPLY_ADD_WAIT_EXISTS' => '你向 {0} 发送的关注请求，他还没有回应，再等等看吧。',
			'REPLY_ADD_WAIT_SUC' => '搞定了！我们已经帮你向 {0} 发送了关注请求，希望能够很快得到{0}的回应。',
			'REPLY_ADD_NOUSER' => '哎呀！抱歉，你添加的 {0} 我不认识，请你输入帐号或手机号、邮件地址。了解更多？发送 HELP。',
			'REPLY_ADD_500' => '哎呀！由于系统故障，发送关注请求失败了……请稍后再试吧。',
			'REPLY_ADD_NOADDRESS' => '哎呀！抱歉，我太笨了。你添加的 {0} 我不认识，请你输入手机号码或邮件地址。了解更多？发送 HELP。',
			'REPLY_ADD_REQUEST_INVITE' => '搞定了！我们已经帮你发出了邀请！期待很快能得到你朋友的回应。',
			'REPLY_ADD_SELF' => '{0}，你好，{1} 是你自己的地址，邀请自己没有意义，邀请别人才有意义，活着就要做有意义的事。',
			
			//Follow Request
			'REPLY_DENY_HELP' => '如果你的叽歪受保护。接受请求发送：accept+空格+别人的名字；删除请求发送：deny+空格+别人的名字；例如“accept 叽歪小助理”',
			'REPLY_FOLLOWREQUEST' => '你好，{0} 设置了隐私保护，为你向 {0} 发送了关注请求，请耐心等待好消息吧。',

			'REPLY_FOLLOWREQUEST_DENY' => '搞定了！你拒绝了 {0} 的关注申请。发送 GET {0} 获取 {0} 的最新更新。',
			'REPLY_FOLLOWREQUEST_ACCEPT' => '搞定了！你已经接受了 {0} ，并成功建立了互相关注关系。',
			'REPLY_FOLLOWREQUEST_CANCEL' => '搞定了！你取消了向 {0} 发出的关注请求。',

			'REPLY_DELETE_SUC' => '搞定了！你已经不再关注 {0} 了。',
			'REPLY_DELETE_HELP' => '关注别人发送：f+空格+别人的名字；取消关注别人发送：f+空格+别人的名字；例如“f 叽歪小助理”“delete 叽歪小助理” ',
			'REPLY_DELETE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_GET_SUC' => '{0}: {1}',
			'REPLY_GET_HELP' => '获取别人最新叽歪发送：get+空格+别人的名字，例如“get 冷笑话”“get 北京天气”“get 处女座运程”“get cctv5” ',
			'REPLY_GET_NOSTATUS' => '还没有更新过。',
			'REPLY_GET_NOPERM' => '哎呀！{0} 设置隐私保护，发送 FOLLOW {0} 等待对方通过核。',

			'REPLY_D_HELP' => '发悄悄话给别人发送：D+空格+别人的名字+空格+悄悄话，例如“D girl 你好漂亮” ',
			'REPLY_D_SUC'	=> '悄悄话已经发送给 {0} ，也许 {0} 会马上回复你的哦。',
			'REPLY_D_NOPERM_BIO' => '你还不在 {0} 的好友列表中，无法悄悄话。你可以发送ADD {0} 将 {0} 添加为好友， 然后期待也被对方添加为好友啦！',
			'REPLY_D_NOPERM' => '哎呀！抱歉，{0} 只接受被其关注的人的悄悄话。',
			'REPLY_D_NOUSER' => '哎呀！没有找到 {0} 这个用户。了解更多？发送 HELP。',

			'REPLY_ON_SUC' => '通知开启。随时发送off都可以关掉通知。关注别人发送：on+空格+别人的名字。回复h获得帮助 ',
			'REPLY_ON_SUC_DM' => '通知开启。随时发送off都可以关掉通知。你有{0}条未阅读的悄悄话，回复dd开始阅读。关注别人发送：on+空格+别人的名字。回复h获得帮助 ',
			'REPLY_ON_ERR' => '哎呀！抱歉，打开通知失败，我们会尽快解决。',
			'REPLY_ON_SUC_USER' => '搞定了！设置接受 {0} 的更新通知成功，发送 OFF {0} 关闭更新通知。',
			'REPLY_ON_ERR_USER' => '哎呀！抱歉，设置接收 {0} 的更新通知失败，我们会尽快解决。',

			'REPLY_OFF_SUC' => '通知消息已关闭。发送on可开启。取消关注别人发送：off+空格+别人的名字，例如“off 叽歪小助理”',
			'REPLY_OFF_ERR' => '哎呀！抱歉，关闭通知失败，我们会尽快解决。',
			'REPLY_OFF_SUC_USER' => '搞定了！设置不接受 {0} 的更新通知成功，发送 ON {0} 可启更新通知。',

			'REPLY_ON_SUC_MUL' => '搞定了！设置接受 {0} 的更新通知成功。',
			'REPLY_OFF_SUC_MUL' => '搞定了！设置不接受 {0} 的更新通知成功。',

			'REPLY_FOLLOW_HELP' => '关注别人发送：f+空格+别人的名字；取消关注别人发送：f+空格+别人的名字；例如“f 叽歪小助理”“delete 叽歪小助理” ',
			'REPLY_FOLLOW_SUC' => '搞定了！已经成功关注 {0} 了！',
			'REPLY_FOLLOW_NOUSER' => '哎呀！抱歉，你关注的 {0} 我不认识，请你确认输入了正确的叽歪帐号。',
			'REPLY_FOLLOW_EXISTS' => '好消息！你已经关注 {0} 了。',

			'REPLY_LEAVE_HELP' => 'LEAVE命令帮助：LEAVE 帐号。',
			'REPLY_LEAVE_SUC' => '你已经取消对 {0} 的关注。',
			'REPLY_LEAVE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_WHOIS_HELP' => '查询别人资料发送：whois+空格+别人的名字，例如“whois 叽歪小助理” ',
			'REPLY_WHOIS_SUC' => '{0}',
			'REPLY_WHOIS_NOUSER' => '哎呀！没有找到 {0} 这个用户。',

			'REPLY_NUDGE_HELP' => '挠挠别人发送：nn+空格+别人的名字，例如“nn 叽歪小助理” ',
			'REPLY_NUDGE_SELF' => '后背痒痒？自己挠是够不着的，我想你得等着别人来挠你了。',
			'REPLY_NUDGE_DENY' => '{0} 已经关闭了通知，不想被挠挠。。。要不你稍后再试吧？',
			'REPLY_NUDGE_SUC' => '我们已经帮你挠挠了 {0} 一下！期待很快能得到你朋友的回应。',
			'REPLY_NUDGE_NOUSER' => '哎呀！没有找到 {0} 这个用户。',
			'REPLY_NUDGE_NOPERM' => '对不起，你暂时不能对 {0} 执行挠挠操作。',

			'REPLY_REG_HELP' => '使用方法：REG 账户 [昵称]，[]内为可选。',
			'REPLY_REG_HELP_GM' => '改名发送：gm+空格+新名字，例如“gm girl”；修改密码发送：mima+空格+新密码 ',
			'REPLY_REG_SUC_NICK' => '好消息，你的昵称修改为 {0} 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SUC_SCREEN' => '好消息，你的帐户名修改为 {0} 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SUC_ALL' => '好消息，修改账户及昵称为 {1} ({0}) 成功，如果不满意，可以重新修改！',
			'REPLY_REG_SAME' => '哇，你本来就叫 {0}，压根不需要改哦。',
			'REPLY_REG_INVALID_NAME' => '你要修改的用户名 {0} 不合法，请重新选择。',
			'REPLY_REG_HOT' => '哇，你要注册的账户名 {0} 太热，请重新选择一个吧。',
			'REPLY_GM_HOT' => '哇，你要修改的账户名 {0} 太热，请重新选择一个吧。',
			'REPLY_REG_500'  => '哎呀！由于系统故障，你的请求失败了…… 请稍后再试吧。',
			'REPLY_REG_REPLY_SUC' => '你好，你已经完成了注册，你的用户名是 {0}',
			'REPLY_CREATE_USER_FIRST' => '恭喜你成功获得用户名 {0} ！改名请回复 GM+空格+新用户名，设置密码请回复 PASS+空格+密码',

			'REPLY_NOREG_TIPS' => '你还没有注册，请回复你想要的账户名。',

			'REPLY_WHOAMI' => '你是 {0}',
			'REPLY_WHOAMI_WEB' => '你是 {0}，你的叽歪档案位于：http://JiWai.de/{0}',
			'REPLY_WHOAMI_SMS' => '你是 {0}，你的叽歪档案位于：http://JiWai.de/{0} ，手机访问：http://m.JiWai.de/{0}',
			'REPLY_WHOAMI_IM' => '你是 {0}，设置密码请来这里：http://JiWai.de/wo/account/complete',

			'REPLY_ACCEPT_HELP' => '如果你的叽歪受保护。接受请求发送：accept+空格+别人的名字；删除请求发送：deny+空格+别人的名字；例如“accept 叽歪小助理”',

			'REPLY_VERIFY_SUC' => '搞定了！你已经通过了验证。回复本消息即可进行更新，耶！',
			'REPLY_VERIFY_FAIL' => '你输入的验证码 {0} 不正确，没能成功绑定，请到 http://jiwai.de/wo/devices/{1} 查看正确验证码再试一次吧',

			/**
			 * 一般回复
			 */
			'REPLY_500'  => '哇，真可怕！由于系统故障，你的请求失败了…… 请稍后再试吧。',
			'REPLY_NOUSER' => '哎呀！没有找到用户 {0}',
			'REPLY_NOTAG' => '哎呀！没有找到 {0}',

			//Out
			'OUT_NUDGE' => '{0} 挠挠了你一下，提醒你更新！回复本消息即可更新。',

			'OUT_ADD_EMAIL' => '{0}({1}) 邀请你来使用我们的服务！',
			'OUT_ADD_IM' => '{0}({1}) 邀请你来使用我们的服务！想对你的朋友说些什么呢？',
			'OUT_ADD_SMS' => '{0}({1}) 邀请你来使用我们的服务！想对你的朋友说些什么呢？(本短信服务免费)',
			'OUT_FOLLOW' => '你好，{2}！{0}( http://JiWai.de/{1}/ ) 关注了你。',

			'OUT_ADDACCEPT_YES_INVITER' => '{0} 已经接受你的邀请，并完成了注册，用户名为：{1}',
			'OUT_ADDACCEPT_YES_INVITEE' => '{0} 你好，你已经成功接受了 {1} 的邀请；回复 HELP 了解更多。',

			'OUT_FOLLOWREQUEST_ACCEPT' => '{0} 已经接受你的关注，并成功与你建立互相关注关系。',
			'OUT_FOLLOWREQUEST_MESSAGE' => '你的叽歪受保护。{0}请求关注你。回复accept {0}接收请求；回复deny {0}删除请求',


			//
			'REPLY_MMS_NOPERM' => '{0}，你好。由于{1}设置了私密，而你还没关注 {1}，不能下载彩信。',
			'REPLY_MMS_NOMMS' => '哇，没搞错吧？没有找到你要的彩信信息。',
			'REPLY_MMS_NOSMS' => '{0}，你没有绑定手机或没有通过手机验证，不能下载彩信。',
			'REPLY_MMS_SUC_IM' => '{0}，{1} 上传的彩信[{2}]即将发往你绑定的手机，请注意查收。',
			'REPLY_MMS_HELP' => '只能使用手机回复 DM 到给定特服号获取彩信。',
			'REPLY_MMS_ILL' => '哇，真可怕！我们不知道你究竟想要作什么！',

			'REPLY_0000_HELP' => '发送 OFF 取消手机信息提示。',

			//Track
			'REPLY_TRACK_SHOW' => '你关注的词汇有：{0}',
			'REPLY_TRACK_HELP' => '关注词汇发送：track+空格+词汇，例如“track 叽歪” ',
			'REPLY_TRACK_SUC' => '你将收到匹配 {0} 的更新，取消关注请输入 UNTRACK {0}',
			'REPLY_UNTRACK_SUC' => '你将不再收到匹配 {0} 的更新',
			'REPLY_UNTRACK_HELP' => '取消关注的词汇发送：untrack+空格+词汇，例如“untrack 叽歪” ',

			//Block
			'REPLY_UNBLOCK_HELP' => '阻止别人发送：block+空格+别人的名字；取消阻止发送：unblock+空格+别人的名字；例如“unblock 叽歪小助理” ',
			'REPLY_BLOCK_HELP' => '阻止别人发送：block+空格+别人的名字；取消阻止发送：unblock+空格+别人的名字；例如“unblock 叽歪小助理” ',
			'REPLY_BLOCK_LIST' => '被你阻止的用户有：{0} ，取消阻止请输入 UNBLOCK 用户名',
			'REPLY_BLOCK_SUC' => '你阻止用户 {0} 成功，将不再会受其侵扰。',
			'REPLY_UNBLOCK_SUC' => '你取消了阻止用户 {0}',

			//PASS
			'REPLY_PASS_SUC' => '你好，{0} 你设置的密码是：{1} ，网页登录请到 http://JiWai.de/wo/login',
			'REPLY_PASS_HELP' => '改名发送：gm+空格+新名字，例如“gm girl”；修改密码发送：mima+空格+新密码 ',

			//Merge
			'REPLY_MERGE_OWN' => '你好，当前设备绑定的用户正是 {0} ，无需合并',
			'REPLY_MERGE_WEBUSER' => '你好，你当前设备用户已经在 WEB 上登录并设定了密码，请联系管理员吧',
			'REPLY_MERGE_SUC' => '你好，当前设备 {0}://{1} 已转移绑定到用户 {2} ，常回来叽歪呀～',
			'REPLY_MERGE_SUC_YIQI' => '合并绑定成功，查询当前登录名请回复whoami，修改登陆密码请回复passwd 新密码。',
			'REPLY_MERGE_MULTI' => '你好，你的当前用户，已经绑定了多个设备，还是继续使用吧',
			'REPLY_MERGE_HAVE' => '你好，{0} 已经绑定了 {1}://{2} ，删除绑定后才能合并过来',
			'REPLY_MERGE_ERR' => '你提供的合并用户名 {0} 和你提供的密码不匹配，无法完成操作',
			'REPLY_MERGE_WEBREQ' => '你好，合并帐户的请求只能从 (MSN/QQ/Skype/GTalk/Yahoo!/SMS) 端发起',
			'REPLY_MERGE_TIPS' => '合并账户发送：{0}+空格+合并到的网站账户名+空格+合并到的网站账户密码 ',
			'REPLY_MERGE_HAVE_YIQI' => '合并失败，已绑定同类软件或移动设备！',

			//Update Status
			'REPLY_UPDATESTATUS_FILTERED' => '对不起，因为含有某些关键词，你发送的叽歪被我们暂时藏起来了！',
			'REPLY_UPDATESTATUS_REPEATED' => '对不起，请不要重复发布相同内容的叽歪！',
			'REPLY_UPDATESTATUS_BLOCKED' => '对不起，对方已经阻止你进行回复！',

			//VOTE
			'REPLY_VOTE_SUC' => '你好，你的投票被接受，谢谢参与！',
			'REPLY_VOTE_SUC_DM' => '@{0} 参与了你的投票活动，选择了选项 {1} : {2} ，请点击以下链接 http://jiwai.de/{3}/statuses/{4} 查看投票结果！',
			'REPLY_VOTE_ERR' => '投票发送：tp+空格+投票代码+空格+选项代码 ，发送时请检查投票代码及选项代码是否正确！ ',
			'REPLY_VOTE_ERR_NOVOTE' => '对不起，不存在的投票项目，请检查投票代码！',
			'REPLY_VOTE_ERR_CHOICE' => '对不起，不存在的投票选项，请检查选项代码！',
			'REPLY_VOTE_ERR_EXPIRE' => '对不起，本次投票活动已过期！',
			'REPLY_VOTE_ERR_WAITIT' => '对不起，本次投票活动尚未开始，请等待！',
			'REPLY_VOTE_ERR_DEVICE' => '对不起，你当前的设备 {1} 不允许参与此投票！',
			'REPLY_VOTE_ERR_EXCEED' => '对不起，你在规定时间投票次数已达上限！',

            //DICT
            'REPLY_DICT_MATCH'  => '{0}:{1} 感谢{2}',
            'REPLY_DICT_GUESS'  => '你要查找的是不是{0}',
            'REPLY_DICT_NIL'    => '对不起，叽歪词典不知道{0}',
            'REPLY_DICT_HELP'   => '用叽歪查字典发送：dict+空格+英文单词或中文单词，例如“dict thanks”“dict 谢谢” ',

			//DD
			'REPLY_DD_NIL' => '目前没有未阅读的悄悄话。发悄悄话给别人发送：D+空格+别人的名字+空格+悄悄话，例如“D girl 你好漂亮” ',
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

	static function GetReplyString( $robot_msg, $shortcut, $value=array(), $options=array() )
	{
		if( $robot_msg ) 
		{
			$server_address = $robot_msg->GetHeader('serveraddress');
			$type = $robot_msg->GetType();
			$address = $robot_msg->GetAddress();

			$conference_id = JWRobotLingoBase::GetLingoConferenceId( $server_address, $address, $type );
		}
		else
		{
			$conference_id = isset($options['conference_id']) ?
				$options['conference_id'] : null;
		}

		$reply_map = self::GetReplyMap($conference_id);
		$shortcut = strtoupper($shortcut);

		return self::FetchReplyString($reply_map, $shortcut, $value);
	}

	static function FetchReplyString($reply_map, $shortcut, $value=array())
	{
		$shortcut = strtoupper( $shortcut );
		if ( isset( $reply_map[ $shortcut ] ) )
		{
			$reply_string = $reply_map[ $shortcut ];

			$va = array_values( $value );
			foreach ($va as $k=>$v) 
			{
				$reply_string = str_replace( '{'.$k.'}', $v, $reply_string ); 
			}
			return $reply_string;
		}
		return null;
	}

	static function GetReplyMap( $conference_id ) {

		$map = self::$msReplyMap;

		switch ($conference_id)
		{
			case 5: // Wu Ye Xin Yu
			{
				$map['REPLY_WHOAMI_IM'] = '您的昵称是{0}，更改昵称请回复：GM + 空格 + 昵称。';
				$map['REPLY_REG_MSG'] = '欢迎您参与《午夜心语》节目，本短信服务不收任何信息费，正常通信费除外，请把你的昵称作为短信内容直接回复。';
				$map['REPLY_REG_SUC'] = '{0}，《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。您可回复"GM + 空格 + 昵称"获得个性昵称。';
				$map['REPLY_UPDATESTATUS'] = '{0}, 《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。';
				$map['REPLY_UPDATESTATUS_IM'] = '{0}, 《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。';
				$map['REPLY_NAMEFULL'] = '午夜过客';
			}
			break;
			case 9: //Qin Zi Gang Wan
			{
				$map['REPLY_WHOAMI_IM'] = '您的昵称是{0}，更改昵称请回复：GM + 空格 + 昵称。';
				$map['REPLY_REG_MSG'] = '欢迎您参与《亲子港湾》节目，本短信服务不收任何信息费，正常通信费除外，请把你的昵称作为短信内容直接回复。';
				$map['REPLY_REG_SUC'] = '感谢您对吉林教育电视台《亲子港湾》栏目的支持，您发送的短信即将播出，敬请关注。输入 A 回复此短信即可免费成为本栏目家长俱乐部的会员。';
				$map['REPLY_FOLLOW_SUC'] = '恭喜您已成为《亲子港湾》家长俱乐部的会员，您在家庭教育中的难题或困惑，我们有专家为您解答，您有成功的成长成才经验，我们期望与您分享。';
				$map['REPLY_UPDATESTATUS'] = '感谢您对吉林教育电视台《亲子港湾》栏目的支持，您发送的短信即将播出，敬请关注。输入 A 回复此短信即可免费成为本栏目家长俱乐部的会员。';
				$map['REPLY_UPDATESTATUS'] = '感谢您对《亲子港湾》栏目的支持，我们在每周六、日晚8：10分邀请专家现场解答您的问题。同时，欢迎您对栏目或您感兴趣的家教话题发表您的见解。';
				$map['REPLY_UPDATESTATUS_IM'] = '感谢您对《亲子港湾》栏目的支持，我们在每周六、日晚8：10分邀请专家现场解答您的问题。同时，欢迎您对栏目或您感兴趣的家教话题发表您的见解。';
			}
			break;
			case 76: //Bao Bao Shu
			{
				$map['REPLY_UPDATESTATUS'] = '我在宝宝树2008华人父母新年许愿活动中，许下一个愿望，愿望地址：www.babytree.com/promo/2008wish/{1} ，快来给我的愿望加油吧。';
				$map['REPLY_UPDATESTATUS_IM'] = '我在宝宝树2008华人父母新年许愿活动中，许下一个愿望，愿望地址：www.babytree.com/promo/2008wish/{1} ，快来给我的愿望加油吧，把链接挂在{2}状态上，让你的朋友给你加油吧。';
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
