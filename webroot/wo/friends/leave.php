<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_REQUEST));

$idLoginedUser=JWUser::GetCurrentUserId();

if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idPageUser 	= $match[1];

		$friend_name	= JWUser::GetUserInfoById($idPageUser,'nameFull');

		if ( JWFollower::Destroy($idPageUser, $idLoginedUser) )
		{
			$notice_html = <<<_HTML_
退订成功，您将不会再在手机或聊天软件上收到${friend_name}的更新。
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，退订${friend_name}失败了……
请稍后再试吧。
_HTML_;
		}
	}

	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);

}


if ( array_key_exists('HTTP_REFERER',$_SERVER) )
	$redirect_url = $_SERVER['HTTP_REFERER'];
else
	$redirect_url = '/';

header ("Location: $redirect_url");
exit(0);
?>
