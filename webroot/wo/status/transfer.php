<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);
$idUser=JWLogin::GetCurrentUserId();
if ( ! JWUser::IsAdmin($idUser) ) {
	JWSession::SetInfo( 'error',"你无权转移这条更新（编号 $idStatus ）" );
}
$id = 0;
if ( preg_match('/^\/(\d+)$/',$_REQUEST['pathParam'],$match) )
{
	$id = abs(intval($match[1]));
}
if ( !$id ) {
	JWSession::SetInfo( 'notice',"转移ID号：{$id} 的更新失败！" );
}

JWDB_Cache::UpdateTableRow('Status', $id, array('idUser'=> 190808 ));
JWSession::SetInfo( 'notice',"转移ID号：{$id} 的更新成功！" );

JWTemplate::RedirectBackToLastUrl('/');
?>
