<?php
require_once( './config.inc.php' );

if( $_POST ) {
	$stockNum = $nameFull = null;
	extract( $_POST, EXTR_IF_EXISTS );

	if( $idUser = JWCommunity_User::CreateUserStock( $stockNum, $nameFull , null ) ) {
		JWDB::SaveTableRow( 'ComStockAccount', array(
					'idUser' => $idUser,
				));

		SetNotice("创立股票账户：$nameFull 成功。", true);
	}
}

JWRender::Display( 'createStockAccount' , array(
				'menu_nav' => 'createStockAccount',
			));
?>
