<?php
require_once( dirname(__FILE__) . '/function.php');

$id = null;
extract($_POST, EXTR_IF_EXISTS);

if( $_POST ) {
	if( $id ) {
		$id = JWDB::CheckInt( $id );
		JWStatus::Destroy( $id );
		setTips("删除ID号 : $id 的更新成功!");
	}
	Header("Location: statusdelete.php");
	exit;
}

$render = new JWHtmlRender();
$render->display("statusdelete", array(
			'menu_nav' => 'statusdelete',
			));
?>
