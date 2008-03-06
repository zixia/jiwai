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
	foreach ( $cb AS $i )
	{
		
		if ( $delete )
		{
			JWQuarantineQueue::DealQueue( $i, JWQuarantineQueue::DEAL_DELE ); 	
			backToGet("删除成功～");
		}

		if ( $allow )
		{
			JWQuarantineQueue::FireStatus( $i ); 	
			backToGet("审核通过～");
		}
	}
}

$statusQuarantine = JWQuarantineQueue::GetQuarantineQueue(JWQuarantineQueue::T_STATUS, null, 0, 20 );

$render = new JWHtmlRender();
$render->display("statusexam", array(
			'menu_nav' => 'statusexam',
			'statusQuarantine' => $statusQuarantine,
			));
?>
