<?php
require_once( dirname(__FILE__) . '/function.php' );

$d = 'deleted';
$page = 1;
$pageSize = 20;
extract($_GET, EXTR_IF_EXISTS);
$page = ($page>1) ? $page : 1;
$offset = ($page-1) * $pageSize;

switch($d){
	case 'deleted':
		$statusNum = JWStatusQuarantine::GetStatusQuarantineNum(JWStatusQuarantine::DEAL_DELETED);
		$statusQuarantine = JWStatusQuarantine::GetStatusQuarantine(JWStatusQuarantine::DEAL_DELETED, $pageSize, $offset);
	break;
	default:
		$statusNum = JWStatusQuarantine::GetStatusQuarantineNum(JWStatusQuarantine::DEAL_ALLOWED);
		$statusQuarantine = JWStatusQuarantine::GetStatusQuarantine(JWStatusQuarantine::DEAL_ALLOWED, $pageSize, $offset);
}
$dictFilter = JWFilterConfig::GetDictFilter();
$pagination = new JWPagination($statusNum, $page);

$render = new JWHtmlRender();
$render->display("statusexamed", array(
			'menu_nav' => 'statusexamed',
			'statusQuarantine' => $statusQuarantine,
			'dictFilter' => &$dictFilter,
			'dealStatus' => $d,
			'pagination' => $pagination,
			));
?>
