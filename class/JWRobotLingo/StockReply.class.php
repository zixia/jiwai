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
		'REPLY_ZC_SUC' => '你已经成功注册为 {0}',
		'REPLY_ZC_SUCC' => '你已经成功注册为 {0}',
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
		$address = $robotMsg->GetAddress();

		$idUserConference = JWRobotLingoBase::GetLingoUser( $serverAddress, $address, $type );

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
