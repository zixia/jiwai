<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$id = null;
extract($_POST, EXTR_IF_EXISTS);

if( $_POST ) {
	if( $id ) {
		$sql = "DELETE FROM Status WHERE id='$id'";
		JWDB::Execute( $sql );
		setTips("删除ID号 : $id 的更新成功!");
	}
	Header("Location: statusdelete");
	exit;
}

$render = new JWHtmlRender();
$render->display("statusdelete", array(
			'menu_nav' => 'statusdelete',
			));
?>
