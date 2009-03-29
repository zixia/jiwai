<?php
require_once( dirname(__FILE__) . '/function.php');

$id = $type = $status = $name = null;
extract($_REQUEST, EXTR_IF_EXISTS);
$id = intval($id);
if( $_GET && "view"==$type)
{
	$status_row = JWDB_Cache_Status::GetDbRowById( $id );
	if(!empty($status_row)) $status=$status_row['status'];
}
if( $_POST ) {
	if( $id ) {
		$id = JWDB::CheckInt( $id );
		if("update"==$type)
		{
			JWDB_Cache::UpdateTableRow('Status', $id, array('status'=>$status));
			setTips("修改ID号 : $id 的更新成功!");
		}
		elseif("delete"==$type)
		{
			JWStatus::Destroy( $id );
			setTips("删除ID号 : $id 的更新成功!");
		}
		elseif("transfer"==$type)
		{
			$idUser = JWUser::GetUserInfo($name, 'id');
			if ( $idUser ) {
				JWDB_Cache::UpdateTableRow('Status', $id, array('idUser'=>$idUser));
				setTips("转移ID号 : $id 的更新成功!");
			} else {
				setTips("转移ID号 : $id 的更新失败!");
			}
		}
	}
	Header("Location: statusdelete.php");
	exit;
}

$render = new JWHtmlRender();
$render->display("statusdelete", array(
			'menu_nav' => 'statusdelete',
			'status' => $status,
			'id' => $id,
			));
?>
