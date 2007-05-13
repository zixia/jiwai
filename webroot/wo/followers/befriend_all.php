<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$logined_user_id	= JWLogin::GetCurrentUserId();

if ( is_int($logined_user_id) )
{
		$follower_ids	= JWFollower::GetFollower($logined_user_id);

		if ( !empty($follower_ids) )
		{
			$is_succ = JWSns::AddFriends($logined_user_id, $follower_ids);

			if ($is_succ )
			{
				$notice_html = <<<_HTML_
您已经成为了您的粉丝的好友（或者已经发送了好友请求）
_HTML_;
			}
			else
			{
				$error_html = <<<_HTML_
哎呀！由于系统故障，好友添加失败了……
请稍后再尝试吧。
_HTML_;
			} 

		}
}

if ( !empty($error_html) )
	JWSession::SetInfo('error',$error_html);

if ( !empty($notice_html) )
	JWSession::SetInfo('notice',$notice_html);


if ( array_key_exists('HTTP_REFERER',$_SERVER) )
	$redirect_url = $_SERVER['HTTP_REFERER'];
else
	$redirect_url = '/';

header ("Location: $redirect_url");
exit(0);
?>
