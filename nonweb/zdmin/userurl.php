<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$n = null;
extract($_REQUEST, EXTR_IF_EXISTS);

if($n) {
	$u = JWUser::GetUserInfo( $n ) ;
	if( $u ) {
		$uArray = array(
			'isUrlFixed' => 'N',
		);
		JWDB::UpdateTableRow('User', $u['id'], $uArray );
		setTips( "允许 $n 再次修改 URL 成功。");
	}
	Header('Location: '. $_SERVER['REQUEST_URI'] );
}

JWRender::display( 'userurl' );
?>
