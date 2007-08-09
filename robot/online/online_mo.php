#!/usr/bin/php
#######################
# used to execute by java process;
#######################
<?php
define( 'CONSOLE', true );
require_once( dirname(__FILE__) . '/../../jiwai.inc.php' );

while ( $line=JWConsole::getline() ){
	if( preg_match('/(\w+)\/\/(.*)\s*:\s*(\w)/', $line, $matches ) ){
		$type = strtolower( $matches[1] );
		$address = strtolower( $matches[2] );
		$status = strtoupper( $matches[3] );

		error_log ( "$type - $address -$status\n", 3, "/tmp/logrobot" );

		JWDevice::UpdateDeviceOnlineStatus($address, $type, $status);
	}else{
		// error_log ( "not match\n", 3, "/tmp/logrobot" );
	}
	//error_log( time().": $line" , 3, "/tmp/logrobot" );
}
?>
