<?php
require_once('../../../jiwai.inc.php');
if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	header_405();
}

$status = null;
extract($_POST, EXTR_IF_EXISTS);
$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !$status ) {
	header_400();
}

$idUser = JWApi::GetAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$device = 'web';
$timeCreate = date("Y-m-d H:i:s");
$status = urlDecode( $status );

if(JWStatus::Create($idUser, $status, $device, $time=null)){
	$insertedId = JWDB::GetInsertedId();
	$status = JWStatus::GetStatusDbRowById( $insertedId );
	switch($type){
		case 'xml':
			renderXmlReturn($status);
		break;
		default:
			renderJsonReturn($status);
	}	
}else{
}

function renderXmlReturn($status){
	$oStatus = JWApi::ReBuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;

	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $oStatus, 0, 'status' );
	echo $xmlString;
}

function renderJsonReturn($status){
	$oStatus = JWApi::RebuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;

	echo json_encode( $oStatus );
}

function header_405(){
	Header("HTTP/1.1 405 Method Not Allowed");
	exit;
}
function header_400(){
	Header("HTTP/1.1 400 Bad Request");
	exit;
}
?>
