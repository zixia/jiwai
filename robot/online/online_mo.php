#!/usr/bin/php
<?php
/**
 * This file is used to sync users' im online status;
 * It would be called by Java processor;
 * I did not read javaProcessor's inputStream, so ****U must not let this function output anything****
 * otherwise the javaProcessor will be hung.
 */ 
define( 'CONSOLE', true );
require_once( dirname(__FILE__) . '/../../jiwai.inc.php' );

while ( $line=JWConsole::getline() ){

	if( preg_match('/^(\w+):\/\/(.*)\/(.*)\/([[:alpha:]]+)\/$/', trim($line), $matches ) ){

		$type = strtolower( $matches[1] );
		$address = strtolower( $matches[2] );
		$serverAddress = strtolower( $matches[3] );
		$status = strtoupper( $matches[4] );

		JWIMOnline::SetIMOnline($address, $type, $serverAddress, $status);

	}
}
?>
