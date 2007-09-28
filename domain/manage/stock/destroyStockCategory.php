<?php
require_once( './config.inc.php' );

if( $_POST ) {
	$nameScreen = null;
	extract( $_POST, EXTR_IF_EXISTS );

	$nameScreen = "stock_$nameScreen";

	$userInfo = JWUser::getUserInfo( $nameScreen );

	if( false == empty( $userInfo ) ){	
		$nameFull = $userInfo['nameFull'];
		SetNotice("成功销毁 股票：$stockNum");
		JWDB::DelTableRow( 'User', array('nameScreen' => "$nameScreen" ) );
		JWDB::DelTableRow( 'Conference', array('idUser' => null ) );
		JWDB::DelTableRow( 'ComStockAccount', array( 'idUser' => null ) );
		JWDB::DelTableRow( 'Category', array('name'=>$nameFull, 'type'=>'STOCK' ) );
	}

	Header('Location: /destroyStockCategory.php');
	exit;
}

JWRender::Display( 'destroyStockCategory' , array(
				'menu_nav' => 'destroyStockCategory',
			));
?>
