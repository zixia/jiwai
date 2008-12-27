<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

if ( array_key_exists('pathParam',$_REQUEST) ){

	$param = $_REQUEST['pathParam'];
	$device_id = null;
	if ( preg_match('/^\/(\d+)$/',$param, $match) ){
		$device_id = $match[1];
		JWSns::DestroyDevice($device_id, $current_user_id);
	}
}

JWTemplate::RedirectBackToLastUrl('/wo/devices/');
?>
