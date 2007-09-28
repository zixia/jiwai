<?php
require_once( dirname(__FILE__) . '/config.inc.php' );
require_once( dirname(__FILE__) . '/lib/Robot.class.php' );

if( $_GET ){
	$cmd = null;
	extract($_GET, EXTR_IF_EXISTS);
	$result = StockCmdRobot::Execute( $cmd ) ;

	if( $result == false ) {
		echo "[robot@stock]$ $cmd\n-bash: $cmd : comand not found. send help for more";
	}else{
		echo "[robot@stock]$ $cmd\n\n$result\n";
	}
}
?>
