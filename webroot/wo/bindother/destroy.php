<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

if ( array_key_exists('pathParam',$_REQUEST) )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param, $match) )
	{
		$bind_id = $match[1];
		JWBindOther::Destroy($current_user_id, $bind_id);
		$notice_html = '解除绑定成功。';
		JWSession::SetInfo('notice', $notice_html);
	}
}

JWTemplate::RedirectBackToLastUrl('/wo/bindother/');
?>
