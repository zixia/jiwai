<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

//die(var_dump($_REQUEST));

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idPageUser 	= $match[1];
		$friendRow = JWUser::GetUserInfo( $idPageUser );

                JWSns::ExecWeb($idLoginedUser, "off $friendRow[nameScreen]", '取消更新通知');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
