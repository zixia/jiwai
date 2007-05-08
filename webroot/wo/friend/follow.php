<?php
require_once ('../../../jiwai.inc.php');

JWUser::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));

$idLoginedUser=JWUser::GetCurrentUserId();

if ( $idLoginedUser )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idPageUser = intval($match[1]);

		$friend_name = JWUser::GetUserInfoById($idPageUser, 'nameFull');

		$is_succ = JWFollower::Create($idPageUser, $idLoginedUser);

		if ($is_succ )
		{
			$notice_html = <<<_HTML_
订阅成功。${friend_name}的更新将会发送到您的手机或聊天软件上。
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统临时故障，您未能成为${friend_name}的粉丝，订阅失败了……
请稍后再试吧。
_HTML_;
		} 

	}
	else // no pathParam?
	{
		$error_html = <<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
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
