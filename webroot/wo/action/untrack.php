<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if ( is_int($current_user_id) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\S+)$/', $param, $match) ){
		$key = $match[1];
		JWSns::ExecWeb($current_user_id, "untrack {$key}", '取消追踪');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
