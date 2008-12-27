<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

//die(var_dump($_REQUEST));

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idPageUser = $match[1];

		$page_user_name	= JWUser::GetUserInfo($idPageUser,'nameFull');

		$bidirection = false;
		if ( JWUser::IsProtected($idLoginedUser) )
			$bidirection = true;

		if ( JWSns::DestroyFriends($idLoginedUser, array($idPageUser), $bidirection) )
		{
			$notice_html = <<<_HTML_
${page_user_name}已经不再关注你了。
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，取消关注失败了……
请稍后再试。
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
