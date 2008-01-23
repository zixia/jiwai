<?php
require_once( dirname(__FILE__) . '/config.inc.php' );
if ( isset($_SESSION[$_SERVER['HTTP_HOST']]) )
{
	unset( $_SESSION[$_SERVER['HTTP_HOST']] );
}
Header('Location: /');
?>
