<?php
require_once( dirname(__FILE__) . '/function.php');

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

$statusQuarantine = JWQuarantineQueue::GetQuarantineQueue(JWQuarantineQueue::T_STATUS, null, 0, 20 );

$dict_filter = JWFilterConfig::GetDictFilter();
$render = new JWHtmlRender();
$render->display("statusexam", array(
			'menu_nav' => 'statusexam',
			'statusQuarantine' => $statusQuarantine,
			'dict_filter' => $dict_filter,
			));
?>
