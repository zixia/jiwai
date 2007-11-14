<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/* 不能用的短指令，会被 50136 模糊匹配
 ( AJ, AD, AP, AM, D, M, J, 9, 28, 23, 25, 6, 8, 21, 26, 22 ) 
 */

/**
 * JiWai.de Robot Lingo Reply Class
 */
class JWRobotLingo_AddReply {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingoReply
	 */
	static private $msInstance;

	static private $msReplyMap = 
	array(
		/**
		 * MMS
		 */
		'REPLY_MMS_NOPERM' => '{0}，你好。由于{1}设置了私密，而你还不被{1}关注，不能下载彩信。',
		'REPLY_MMS_NOMMS' => '哇，没搞错吧？没有找到你要的彩信信息。',
		'REPLY_MMS_NOSMS' => '{0}，你没有绑定手机或没有通过手机验证，不能下载彩信。',
		'REPLY_MMS_SUC_IM' => '{0}，{1}上传的彩信[{2}]即将发往你绑定的手机，请注意查收。',
		'REPLY_MMS_HELP' => '只能使用手机回复 DM 到给定特服号获取彩信。',
		'REPLY_MMS_ILL' => '哇，真可怕！我们不知道你究竟想要作什么！',

		/**
		 * F, 回复F关注好友
		 */
		'REPLY_F_HOT' => '{0}，你好，你选的用户名太热，请回复F+空格+你想要的用户名，自动注册并关注你的好友。',
		'REPLY_F_SELF' => '{0}，你好，{1} 是你自己的地址，邀请自己没有意义，邀请别人才有意义，活着就要做有意义的事。',
		'REPLY_F_SUC_Y_FOLLOW' => '{0}，你好，每当 {1} 更新，你都会收到消息。如果要撤销，请发送LEAVE {2}。发送HELP了解更多。',
		'REPLY_F_SUC_N_FOLLOW' => '{0}，你好，你已成功注册，发送HELP了解更多，修改密码请到 http://JiWai.de/wo/account/complete', 
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
		return JWRobotLingoReply::FetchReplyString( self::$msReplyMap, $shortcut, $value );
	}
}
?>
