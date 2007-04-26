<?php
require_once ('../../jiwai.inc.php');

JWUser::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$idUser=JWUser::GetCurrentUserId();
if ( is_int($idUser) )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idFriend = intval($match[1]);


		$is_succ = JWFriend::Create($idFriend, $idUser);

		if ($is_succ )
		{
			$info_html = <<<_HTML_
好友添加成功，耶！
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！好友添加失败了……
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
