<?php
require_once ('../../jiwai.inc.php');

JWUser::MustLogined();

//die(var_dump($_REQUEST));

$idUser=JWUser::GetCurrentUserId();

if ( is_int($idUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idFriend = $match[1];

		if ( JWFriend::Destroy($idFriend, $idUser) )
		{
			$info_html = <<<_HTML_
好友删除成功，耶！
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！好友删除失败了……
_HTML_;
		}
	}
}


if ( array_key_exists('HTTP_REFERER',$_SERVER) )
	$redirect_url = $_SERVER['HTTP_REFERER'];
else
	$redirect_url = '/';

header ("Location: $redirect_url");
exit(0);
?>
