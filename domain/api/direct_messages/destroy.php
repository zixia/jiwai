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
  * Get Message Information from db
  */
$idMessage = $type = null;
@list($idMessage, $type)= explode('.', trim( $pathParam, '/' ));
if( !$idMessage ) {
	header_400();
}
$unMessage = JWMessage::GetMessageDbRowById( $idMessage );
if( !$unMessage ) {
	header_404();
}

/**
  * message's owner check 
  */
if( $unMessage['idUserReceiver'] != $idUser ) {
	header_403();
}

/**
  * Destroy direct message by Id
  */
if( JWMessage::Destroy($idMessage) ){
	switch($type){
		case 'xml':
			renderXmlReturn($unMessage);
		break;
		default:
			renderJsonReturn($unMessage);
	}	
}else{
	header_500();
}

function renderXmlReturn($message){
	$oMessage = JWApi::RebuildMessage($message);
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $oMessage, 0, 'direct_message' );
	echo $xmlString;
}

function renderJsonReturn($message){
	$oMessage = JWApi::RebuildMessage($message);
	echo json_encode( $oMessage );
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
