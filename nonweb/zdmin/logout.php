<?php
require_once( dirname(__FILE__) . '/function.php');

if ( isset( $_SESSION['idUser'] ) )
	$_SESSION['idUser'] = null;

JWRender::display("logout", array());
?>
