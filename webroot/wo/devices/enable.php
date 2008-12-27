<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

$user_id	= JWLogin::GetCurrentUserId();
$pathParam 	= $_REQUEST['pathParam'];

$isSignatureRecord = isset($_POST['isSignatureRecord']) ? $_POST['isSignatureRecord'] : null;
$enabled_for = null;

if ( !preg_match('/(\d+)/',$pathParam, $matches) )
{
	echo "设备编号错误！";
	JWLog::Instance()->Log(LOG_ERR, '/devices/enable'.$pathParam . ' cant get device id');
	JWTemplate::RedirectBackToLastUrl();
}
$device_id = intval($matches[1]);

if ( ! JWDevice::IsUserOwnDevice($user_id, $device_id) )
{
	echo "未绑定此设备！";
	JWLog::Instance()->Log(LOG_ERR, '/devices/enable'.$pathParam . "  is not owned by $user_id");
	JWTemplate::RedirectBackToLastUrl();
}

if ( isset($_POST['device']) )
{
	$enabled_for	= $_POST['device']['enabled_for'];
}

//Set enabled
$is_succ = JWDevice::SetDeviceEnabledFor($device_id,$enabled_for,$isSignatureRecord);
if($is_succ)
	echo "设置成功！";
else
	echo "设置失败！";
	
if( isset( $_SERVER['HTTP_AJAX'] ) && $_SERVER['HTTP_AJAX'] ) {
}else{
    JWTemplate::RedirectBackToLastUrl('/wo/devices/');
}
exit(0);
?>
