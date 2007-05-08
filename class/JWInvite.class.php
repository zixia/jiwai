<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Invite Class
 */
class JWInvite {
	/**
	 * Instance of this singleton
	 *
	 * @var JWInvite
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWInvite
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
	 *	当前用户邀请邮件地址加入网站
	 *	@emails		array	被邀邮件地址的array
	 *	@message	string	用户写给被邀者的话
	 *	@reciprocal	bool	用户之间是否缺省加为好友
	 *	@return		bool	成功/失败
	 */
	static public function Invite($emails,$message='',$reciprocal=false)
	{
		$user = JWUser::GetCurrentUserInfo();
		foreach ( $emails as $email )
		{
			JWMail::SendMailInvitation($user, $email, $message, $code);
		}
		return true;
	}
}
?>
