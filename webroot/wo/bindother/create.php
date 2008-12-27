<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$bid = $service = $login_name = $login_pass = null;
$sync_conference = $sync_reply = 'N';
extract($_POST, EXTR_IF_EXISTS);
$current_user_id = JWLogin::GetCurrentUserId();
$service_name = ucwords($service);

if ( $login_name && $login_pass )
{
	$options = array(
			'sync_reply' => $sync_reply,
			'sync_conference' => $sync_conference,
			);

	if (JWBindOther::Create($current_user_id, $login_name, $login_pass, $service, $options ))
	{
		$notice_html = '绑定 '.$service_name.' 成功。';
		JWSession::SetInfo('notice', $notice_html);
	}
	else
	{
		$error_html = $service_name . ' 用户名 或 密码 错误。';
		JWSession::SetInfo('error', $error_html);
	}
} 
else if ( $bid )
{
	$options = array(
			'syncReply' => $sync_reply,
			'syncConference' => $sync_conference,
			);
	$is_succ = JWDB::UpdateTableRow( 'BindOther', $bid, $options );
	if($is_succ)
		JWSession::SetInfo('notice', '修改同步选项成功！');
	else
		JWSession::SetInfo('notice', '修改同步选项失败！');
}

JWTemplate::RedirectBackToLastUrl('/wo/bindother/');
?>
