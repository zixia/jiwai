<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$updateResult = JWStatusCopyForManager::UpdateToStatus();

setTips("管理用更新已经同用户更新数据表同步完成，耗时：". $updateResult['timeCost'] );

$render = new JWHtmlRender();
$render->display("statusupdate", array(
			'menu_nav' => 'statusupdate',
			'updateResult' => $updateResult,
			));
?>
