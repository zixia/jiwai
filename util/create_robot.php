#!/usr/bin/php -q
<?php
require_once( '/opt/jiwai.de/jiwai.inc.php' );
if( count( $argv ) < 3 )  {
	print "Usage: create_robot.php type serverAddress\n";
	exit;
}
$type = strtolower($argv[1]);
$serverAddress = strtolower($argv[2]);

switch( $type ){
	case 'msn':
	case 'skype':
	case 'gtalk':
	case 'qq':
		JWIMOnline::Create( $serverAddress, $type, $serverAddress, 'ONLINE' );
		print "Create Success\n";
	break;
	default:
		print "Device: $type not support.\n";
}
?>
