<?php
require_once("../../../jiwai.inc.php");

JWLogin::MustLogined(false);

$logined_user_id	= JWLogin::GetCurrentUserId();

$setting	= @$_REQUEST['current_user'];

if ( isset($setting) )
	JWUser::SetSendViaDevice($logined_user_id, $setting['send_via']);
/*	
$device_row			= JWDevice::GetDeviceRowByUserId($logined_user_id);

$active_options = array();

$supported_device_types = JWDevice::GetSupportedDeviceTypes();

foreach ( $supported_device_types as $type )
{
	if ( isset($device_row[$type]) 
				&& $device_row[$type]['verified']  )
	{	
		$active_options[$type]	= true;
	}
	else
	{
		$active_options[$type] 	= false;
	}
}

JWTemplate::sidebar_jwvia($active_options, $setting['send_via'], true);
*/
?>
