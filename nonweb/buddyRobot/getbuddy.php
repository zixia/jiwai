#!/usr/bin/php -q
<?php
define( 'CONSOLE', true );
require_once( '../../jiwai.inc.php' );

if( $line = trim(JWConsole::getline()) )
{
	$info_string = Base64_Decode( $line );
	$info = @unserialize( $info_string );

	if ( false == empty( $info ) && is_array( $info ))
	{
		list( $type, $username, $password ) = $info ;
		JWBuddy_Robot::GetBuddyList( $type, $username, $password );	
	}
}
?>
