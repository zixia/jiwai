<?php
require_once('./function.php');

$un = null;
$im = null;

$unResult = JWConference::GetDbRowEnableAll();

$render = new JWHtmlRender();
$render->display("conflist", array(
			'menu_nav' => 'conflist',
			'unResult' => $unResult,
			));
?>
