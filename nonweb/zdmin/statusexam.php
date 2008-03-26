<?php
require_once( dirname(__FILE__) . '/function.php');

$page = null;
extract( $_GET, EXTR_IF_EXISTS );

function backToGet($string=null)
{
	setTips( $string );
	Header('Location: statusexam.php');
	exit;
}

if( $_POST )
{
	$delete = $allow = $cb = null;
	extract( $_POST, EXTR_IF_EXISTS );
	if ( empty($cb) ) 
{
		backToGet();
	}
	foreach ( array_values($cb) AS $i )
	{
		
		if ( $delete )
		{
			JWQuarantineQueue::DealQueue( $i, JWQuarantineQueue::DEAL_DELE ); 	
		}

		if ( $allow )
		{
			JWQuarantineQueue::FireStatus( $i ); 	
		}
	}

	if ( $cb )
	{
		if ( $delete )
		{
			backToGet("删除成功～");
		}
		else if ( $allow )
		{
			backToGet("审核通过～");
		}
	}	
}

$page_size = 20;
$page = abs(intval($page)) ? abs(intval($page)) : 1;
$offset = $page_size * ($page-1);

$total_count = JWQuarantineQueue::GetQuarantineQueueNum(JWQuarantineQueue::T_STATUS);
$statusQuarantine = JWQuarantineQueue::GetQuarantineQueue(JWQuarantineQueue::T_STATUS, null, $offset, $page_size);

$pagination = new JWPagination($total_count, $page, $page_size);

{{{
	$page_string = null;
	$pages = 4;
	$l = $pagination->GetPageNo() - $pages;
	if ($l<1) 
		$l = 1;
	$r = $l + $pages*2;
	if ($r>$pagination->GetOldestPageNo()) 
		$r = $pagination->GetOldestPageNo();
	for ($i=$l;$i<$r+1;$i++) 
	{
		$u = $i == $pagination->GetPageNo() ? '' : JWPagination::BuildPageUrl($_SERVER['REQUEST_URI'], $i);
		if ($u)
			$page_string .= "<a href=\"$u\" style=\"color:#00F; margin-left:15px;\">[$i]</a>&nbsp;";
        	else 
			$page_string .= "<a style=\"background:#fff; color:#000; margin-left:15px;\">[$i]</a>";
	}
}}}

$dict_filter = JWFilterConfig::GetDictFilter();
$render = new JWHtmlRender();
$render->display("statusexam", array(
			'menu_nav' => 'statusexam',
			'statusQuarantine' => $statusQuarantine,
			'dict_filter' => $dict_filter,
			'page_string' => $page_string,
			));
?>
