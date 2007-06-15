<?php
require_once('../../../jiwai.inc.php');
$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

/**
  * User Authentication
  */
$idUser = JWApi::getAuthedUserId();
if( !$idUser ){
	JWApi::RenderAuth();
}

/**
  * Get Status Information from db
  */
$idStatus = $type = null;
@list($idStatus, $type)= explode('.', trim( $pathParam, '/' ));
if( !$idStatus ) {
	header_400();
}
$unStatus = JWStatus::GetStatusDbRowById( $idStatus );
if( !$unStatus ) {
	header_404();
}

/**
  * status's owner check 
  */
if( $unStatus['idUser'] != $idUser ) {
	header_403();
}

/**
  * Destroy direct status by Id
  */
if( JWStatus::Destroy($idStatus) ){
	switch($type){
		case 'xml':
			renderXmlReturn($unStatus);
		break;
		default:
			renderJsonReturn($unStatus);
	}	
}else{
	header_500();
}

function renderXmlReturn($status){
	$oStatus = JWApi::RebuildStatus($status);
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
function header_404(){
	Header("HTTP/1.1 404 Not Found");
	exit;
}
function header_403(){
	Header("HTTP/1.1 403 Access Denied");
	exit;
}
function header_400(){
	Header("HTTP/1.1 400 Bad Request");
	exit;
}
function header_500(){
	Header("HTTP/1.1 500 Server Internal Error");
	exit;
}
?>
