<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Balloon Class
 */
class JWBalloonMsg extends JWBalloon{
	/**
	 * Instance of this singleton
	 *
	 * @var JWBalloon
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWBalloon
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

	/**
	 */
	static function CreateUser($idUser)
	{
		$html = <<<_HTML_
欢迎来到叽歪de，
先看看<a href="BALLOON_URL:http://help.jiwai.de/NewUserGuide" target="_blank">《新手手册》</a>，
或者<a href="BALLOON_URL:">以后再说</a>。
_HTML_;

		return self::Create($idUser,$html);
	}

	/**
	 */
	static function CreateFriend($idUser,$idFriend)
	{
		$user_row = JWUser::GetUserInfo($idUser);

		$html = <<<_HTML_
你被 $user_row[nameFull]($user_row[nameScreen]) 关注了。
<a href="BALLOON_URL:/wo/friendships/create/$user_row[idUser]">关注 $user_row[nameFull]($user_row[nameScreen]) </a>
或者<a href="BALLOON_URL:">以后再说</a>。
_HTML_;

        if( false == JWFollower::IsFollower($idFriend, $idUser) )
            self::Create($idFriend,$html);
	}

	/**
	 *	有关注申请
	 */
	static function CreateFollowerRequest($idUser, $idFollower, $note='')
	{
		$user_row = JWUser::GetUserInfo($idFollower);
		$n = $note ? '(<a class="tipped" href="#" onclick="return false;" title="'.htmlspecialchars($note).'">附言</a>)' : '';
		$html = <<<_HTML_
$user_row[nameScreen] 希望和你成为好朋友{$n}。
<a href="BALLOON_URL:/wo/friend_requests/accept/$idFollower">同意</a>
<a href="BALLOON_URL:/wo/friend_requests/deny/$idFollower">不要</a>
或者<a href="BALLOON_URL:">以后再说</a>。
_HTML_;

		self::Create($idUser,$html);
	}

	/**
	 *	被人订阅
	 */
	static function CreateFollower($idUser,$idFollower)
	{
		$follower_row = JWUser::GetUserInfo($idFollower);

		$html = <<<_HTML_
$follower_row[nameFull]($follower_row[nameScreen]) 订阅了你的叽歪。
<a href="BALLOON_URL:/wo/followings/follow/$idFollower">订阅 $follower_row[nameFull]</a>
或者<a href="BALLOON_URL:">以后再说</a>。
_HTML_;

		//self::Create($idUser,$html);

	}

	/**
	 *	加好友申请被通过
	 */
	static function AcceptFriend($idUser,$idAccepter)
	{
		$accepter_row = JWUser::GetUserInfo($idAccepter);

		$html = <<<_HTML_
$accepter_row[nameFull]($accepter_row[nameScreen]) 通过了你的好朋请求。
<a href="BALLOON_URL:">嗯，知道了</a>。
_HTML_;

		self::Create($idUser,$html);

	}

	/**
	 *	上次登录后被挠挠过(无法发至IM或SMS的)
	 */
	static function NudgeUser($idUser,$idNudger)
	{
		$nudger_row = JWUser::GetUserInfo($idNudger);

		$html = <<<_HTML_
$nudger_row[nameFull]($nudger_row[nameScreen]) 挠挠了你一下，提醒你更新叽歪。
<a href="BALLOON_URL:">嗯，知道了</a>。
_HTML_;

		self::Create($idUser,$html);
	}


	/**
	 *	将 BALLOON: 替换，让用户在访问功能链接之前，能够删除对应的 balloon
	 *
	 */
	static public function FormatMsg($idBalloon, $html)
	{
		$html = str_replace(	 'BALLOON_URL:'
								,"/wo/balloon/d/$idBalloon/"
								,$html
					);
		return $html;
	}
}
?>
