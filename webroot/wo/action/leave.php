<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if ( is_int($current_user_id) )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/', $param, $match) ){
		$user_id = $match[1];
		$name = JWUser::GetUserInfo($user_id, 'nameScreen');
		JWSns::ExecWeb($current_user_id, "leave {$name}", '取消关注');
	}
}
JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
