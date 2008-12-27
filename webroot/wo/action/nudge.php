<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();
$current_user_row = JWUser::GetUserInfo($current_user_id);

if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( ! preg_match('/^\/(\d+)$/', $param, $match) )
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
	else
	{
		$nudged_user_id = intval($match[1]);
		$nudged_user_row = JWUser::GetUserInfo($nudged_user_id);
		JWSns::ExecWeb($current_user_id, "nudge {$nudged_user_row['nameScreen']}", '挠挠此人');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit(0);
?>
