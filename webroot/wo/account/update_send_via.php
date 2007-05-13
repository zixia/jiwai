<?php
require_once("../../../jiwai.inc.php");

JWLogin::MustLogined();

$logined_user_id	= JWLogin::GetCurrentUserId();

$setting	= @$_REQUEST['current_user'];

if ( isset($setting) )
	JWUser::SetSendViaDevice($logined_user_id, $setting['send_via']);
?>
