<?php
require_once( dirname(__FILE__) . '/function.php');

$updateResult = JWStatusCopyForManager::UpdateToStatus();

setTips("管理用更新已经同用户更新数据表同步完成，耗时：". $updateResult['timeCost'] );

$render = new JWHtmlRender();
$render->display("statusupdate", array(
			'menu_nav' => 'statusupdate',
			'updateResult' => $updateResult,
			));
?>
