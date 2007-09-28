<?php
require_once( './config.inc.php' );

$nameScreen = $nameFull = $idParent = null;

if( $_POST ) {

	extract( $_POST, EXTR_IF_EXISTS );
	$nameScreen = strtolower( 'stock_'. $nameScreen );

	$idUser = JWCommunity_User::CreateUserStockCategory($nameScreen, $nameFull);
	
	if( false == $idUser ) {
		SetNotice( "已经建立同名账户" );
		Header('Location: /createStockCategory.php');
		exit;
	}
		
	$idCategory = JWCategory::Create( $nameFull, $idParent );

	if( $idUser && $idCategory ) {
		JWDB::SaveTableRow(
			'ComStockAccount', array(
				'idUser' => $idUser,
				'idCategory' => $idCategory,
			)
		);

		SetNotice( "idUser:$idUser, idCategory: $idCategory" );
		Header('Location: /createStockCategory.php');
		exit;
	}
}

$topCategory = JWCategory::GetSonCategory(0, JWCategory::T_STOCK);

JWRender::Display( 'createStockCategory' , array(
				'menu_nav' => 'createStockCategory',
				'topCategory' => $topCategory,
				'idParent' => $idParent,
			));
?>
