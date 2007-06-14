<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
$id = null;
$type = null;
# by zixia: add EXTR_IF_EXISTS
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	exit;
}

@list($id, $type) = explode( ".", $pathParam, 2);

if( is_numeric($id) ){
	switch( $type ){
	case 'json':
		renderJsonStatuses($id);
	break;
	case 'xml':
		renderXmlStatuses($id);
	break;
	default:
		exit;
	}
}

function renderJsonStatuses($id){
	$status = $user = null;
	if( getMessage( $id, $status, $user )){
		$userInfo = JWApi::ReBuildUser($user);
		$statusInfo = JWApi::ReBuildStatus($status);
		$statusInfo['user'] = $userInfo;
		echo json_encode( $statusInfo );
	}
}

function renderXmlStatuses($id){
	$status = $user = $xmlString = null;
	if( getMessage( $id, $status, $user )){
		$userInfo = JWApi::ReBuildUser($user);
		$statusInfo = JWApi::ReBuildStatus($status);
		$statusInfo['user'] = $userInfo;

		header('Content-Type: application/xml; charset=utf-8');
		$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xmlString .= "<status>\n";
		$xmlString .= JWApi::ArrayToXml( $statusInfo, 1 );
		$xmlString .= "</status>\n";
		echo $xmlString;
	}
}

function getMessage($id, &$status, &$user){
	$status = JWStatus::getStatusDbRowById($id);
	if( $status ){
		$user = JWUser::getUserDbRowById($status['idUser']);
		return true;
	}
	return false;
}
?>
