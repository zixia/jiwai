<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_REQUEST));

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idPageUser 	= $match[1];

		$friend_name	= JWUser::GetUserInfo($idPageUser,'nameScreen');
		JWSns::ExecWeb($idLoginedUser, "leave $friend_name", '取消关注');
	}
}
JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
