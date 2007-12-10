<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$un = null;
$im = null;
//extract($_GET, EXTR_IF_EXISTS);

$unResult = JWConference::GetDbRowEnableAll();

$render = new JWHtmlRender();
$render->display("conflist", array(
			'menu_nav' => 'conflist',
			'unResult' => $unResult,
			));
?>
