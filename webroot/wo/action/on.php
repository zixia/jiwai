<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/', $param, $match) ){
		$friend_user_id = intval($match[1]);
		$friend_row = JWUser::GetUserInfo( $friend_user_id );
                JWSns::ExecWeb($current_user_id, "on {$friend_row['nameScreen']}", '接收更新通知');
	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
