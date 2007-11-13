<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$logined_user_id 	= JWLogin::GetCurrentUserId();
$logined_user_row	= JWUser::GetUserInfo($logined_user_id);

if ( $logined_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( ! preg_match('/^\/(\d+)$/',$param,$match) )
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
	else
	{
		$nudged_user_id = intval($match[1]);
		$nudged_user_rows	= JWUser::GetUserDbRowsByIds(array($nudged_user_id));
		$nudged_user_row	= $nudged_user_rows[$nudged_user_id];

		JWSns::ExecWeb($logined_user_id, "nudge $nudged_user_row[nameScreen]", '挠挠此人');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
