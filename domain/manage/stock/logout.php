<?php
require_once( dirname(__FILE__) . '/config.inc.php' );
if ( isset($_SESSION['idUser']) )
{
	unset( $_SESSION['idUser'] );
}
Header('Location: /');
?>
