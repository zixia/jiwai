<?php
require_once( './config.inc.php' );

if( $_POST ) {
	$stockNum = null;
	extract( $_POST, EXTR_IF_EXISTS );

	SetNotice("成功销毁 股票：$stockNum");
	JWDB::DelTableRow( 'User', array('nameScreen' => "gp$stockNum" ) );
	JWDB::DelTableRow( 'Conference', array('idUser' => null ) );
	JWDB::DelTableRow( 'ComStockAccount', array( 'idUser' => null ) );

	Header('Location: /destroyStockAccount.php');
	exit;
}

JWRender::Display( 'destroyStockAccount' , array(
				'menu_nav' => 'destroyStockAccount',
			));
?>
