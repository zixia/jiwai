<?php
require_once("../../../jiwai.inc.php");

$device = null;
extract($_REQUEST, EXTR_IF_EXISTS);
$device = strtolower( $device );

$allowed_array = array( 'msn','sms','qq','gtalk','skype','yahoo','jabber','aol','newsmth','fetion','web','none' );

$user_id = JWApi::GetAuthedUserId();
if( ! $user_id ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

if ( false == in_array( $device, $allowed_array ) )
{
	JWApi::OutHeader(403, true);
}

if ( 'none' == $device )
	$device = 'web';

$device_rows = JWDevice::GetDeviceRowByUserId($user_id);


$user_devices = array_values(array_keys( $device_rows ));
array_push($user_devices, 'web');

if ( false == in_array($device, $user_devices) )
{
	JWApi::OutHeader(406, true);
}

JWUser::SetSendViaDevice($user_id, $device);
?>
