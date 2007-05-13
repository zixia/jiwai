<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$idLoginedUser=JWLogin::GetCurrentUserId();
if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idPageUser = intval($match[1]);

		$page_user_name	= JWUser::GetUserInfo($idPageUser,'nameFull');

		$is_succ = JWSns::AddFriends($idLoginedUser, array($idPageUser));

		if ($is_succ )
		{
			$notice_html = <<<_HTML_
已经将${page_user_name}添加为好友，耶！
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
