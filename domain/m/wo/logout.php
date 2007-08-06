<?php
require_once( '../config.inc.php' );
JWLogin::Logout();
header( 'Location: '.buildUrl( '/' ) );
exit(0);
?>
