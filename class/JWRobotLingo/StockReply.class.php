<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Reply Class
 */
class JWRobotLingo_StockReply {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingoReply
	 */
	static private $msInstance;

	static private $msReplyMap = 
	array(
		'REPLY_ZX_HELP' => '呀，ZX命令用法：(ZX 用户名 昵称)。',
		'REPLY_ZX_YET' => '你已经注册为 {0}，无需再次注册。',
		'REPLY_ZX_SUC' => '你已经成功注册为 {0}。',
		'REPLY_ZX_SUC_F' => '你已经成功注册为 {0}，并订阅了{1}。',
		'REPLY_ZX_HOT' => '你选择的用户名太热，请重新选择一个吧。',
	);

	static function GetReplyString( $robotMsg, $shortcut, $value=array() )
	{
		return JWRobotLingoReply::FetchReplyString( self::$msReplyMap, $shortcut, $value );
	}
}
?>
