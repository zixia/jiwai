<?php
require_once('../../../jiwai.inc.php');
$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

/**
  * User Authentication
  */
$idUser = JWApi::getAuthedUserId();
if( !$idUser ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$idStatus = $type = null;
@list($idStatus, $type)= explode('.', trim( $pathParam, '/' ));
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( !$idStatus ) {
	JWApi::OutHeader(400, true);
}

$idExist = JWFavourite::IsFavourite($idUser, $idStatus);
if( $idExist ) {
	JWApi::OutHeader(406, true);
}else{
	if( false == JWFavourite::Create($idUser, $idStatus) ){
		JWApi::OutHeader(406, true);
	}
}

switch($type){
	case 'xml':
		renderXmlReturn($idStatus);
		break;
	case 'json':
		renderJsonReturn($idStatus);
		break;
	default:
		JWApi::OutHeader(400, true);
}	

function renderJsonReturn($idStatus){
	$status = $user = null;
	if( getStatus( $idStatus, $status, $user )){
		$userInfo = JWApi::ReBuildUser($user);
		$statusInfo = JWApi::ReBuildStatus($status);
		$statusInfo['user'] = $userInfo;
		echo json_encode( $statusInfo );
	}else{
		JWApi::OutHeader(404, true);
	}
}

function renderXmlReturn($idStatus){
	$status = $user = $xmlString = null;
	if( getStatus( $idStatus, $status, $user )){
		$userInfo = JWApi::ReBuildUser($user);
		$statusInfo = JWApi::ReBuildStatus($status);
		$statusInfo['user'] = $userInfo;

		header('Content-Type: application/xml; charset=utf-8');
		$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xmlString .= "<status>\n";
		$xmlString .= JWApi::ArrayToXml( $statusInfo, 1 );
		$xmlString .= "</status>\n";
		echo $xmlString;
	}else{
		JWApi::OutHeader(404, true);
	}
}

function getStatus($idStatus, &$status, &$user){
	$status = JWDB_Cache_Status::GetDbRowById($idStatus);
	if( $status ){
		$user = JWDB_Cache_User::GetDbRowById($status['idUser']);
		return true;
	}
	return false;
}
?>
