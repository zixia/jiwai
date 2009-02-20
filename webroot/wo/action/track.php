<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\S+)$/', $param, $match) ){
		$key = $match[1];
		JWSns::ExecWeb($current_user_id, "track {$key}", '追踪');
	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
