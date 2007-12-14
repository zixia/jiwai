<?php
require_once( dirname(__FILE__) . '/function.php');

$file = JWFilterConfig::GetDictFilename();

$fr = file_get_contents( $file );

$render = new JWHtmlRender();
$render->display("filterdict", array(
			'fresult' => $fr,
			'menu_nav' => 'filter_nav',
			));
?>
