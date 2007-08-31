<?php
require_once("../../../jiwai.inc.php");

$pathParam = isset( $_REQUEST['pathParam'] ) ? $_REQUEST['pathParam'] : null;
@list($idUser,$_) = explode('.', trim( $pathParam, '/' ));

$id = intval( $idUser );


$num = "***********";
if( $id ){
	$device_row = JWDevice::GetDeviceRowByUserId( $id );
	if( false == empty( $device_row ) ) {
		if( isset( $device_row['sms'] ) ){
			$num = preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]****$m[3]";'), $device_row['sms']['address']);
		}
	}
}
echo $num;
?>
