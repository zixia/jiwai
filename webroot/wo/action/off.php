<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if ( is_int($current_user_id) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/', $param, $match) ){
		$friend_user_id = $match[1];
		$friend_row = JWUser::GetUserInfo( $friend_user_id );
                JWSns::ExecWeb($current_user_id, "off {$friend_row['nameScreen']}", '取消更新通知');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
