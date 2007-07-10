<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$file = './dictionary/filterdict.txt';

$fr = file_get_contents( $file );

$render = new JWHtmlRender();
$render->display("filterdict", array(
			'fresult' => $fr,
			'menu_nav' => 'filter_nav',
			));
?>
