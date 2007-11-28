<?php
require_once( dirname(__FILE__) . '/function.php');

$render = new JWHtmlRender();
$render->display("sidebarprev", array(
	'menu_nav' => 'sidebarprev',
	'data' => $_SESSION['sidebarprev'],
));
?>
