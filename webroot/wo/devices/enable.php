<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

$user_id	= JWLogin::GetCurrentUserId();
$pathParam 	= $_REQUEST['pathParam'];

$isSignatureRecord = isset($_REQUEST['isSignatureRecord']) ? $_REQUEST['isSignatureRecord'] : null;
$enabled_for = null;

if ( !preg_match('/(\d+)/',$pathParam, $matches) )
{
	JWLog::Instance()->Log(LOG_ERR, '/devices/enable'.$pathParam . ' cant get device id');
	JWTemplate::RedirectBackToLastUrl();
}
$device_id = intval($matches[1]);

if ( ! JWDevice::IsUserOwnDevice($user_id, $device_id) )
{
	JWLog::Instance()->Log(LOG_ERR, '/devices/enable'.$pathParam . "  is not owned by $user_id");
	JWTemplate::RedirectBackToLastUrl();
}

if ( isset($_REQUEST['device']) )
{
	$enabled_for	= $_REQUEST['device']['enabled_for'];
}

//Set enabled
JWDevice::SetDeviceEnabledFor($device_id,$enabled_for,$isSignatureRecord);
	
if( isset( $_SERVER['HTTP_AJAX'] ) && $_SERVER['HTTP_AJAX'] ) {
}else{
    JWTemplate::RedirectBackToLastUrl('/wo/devices/');
}
exit(0);
?>
