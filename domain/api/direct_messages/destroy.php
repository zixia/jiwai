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
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( !$idMessage ) {
	JWApi::OutHeader(400, true);
}
$unMessage = JWMessage::GetMessageDbRowById( $idMessage );
if( !$unMessage ) {
	JWApi::OutHeader(404, true);
}

/**
  * message's owner check 
  */
if( $unMessage['idUserReceiver'] != $idUser ) {
	JWApi::OutHeader(403, true);
}

/**
  * Destroy direct message by Id
  */
if( JWMessage::Destroy($idMessage) ){
	switch($type){
		case 'xml':
			renderXmlReturn($unMessage);
		break;
		case 'json':
			renderJsonReturn($unMessage);
		break;
		default:
			JWApi::OutHeader(400, true);
	}	
}else{
	JWApi::OutHeader(500, true);
}

function renderXmlReturn($message){
	$oMessage = JWApi::RebuildMessage($message);

	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $oMessage, 0, 'direct_message' );
	echo $xmlString;
}

function renderJsonReturn($message){
	$oMessage = JWApi::RebuildMessage($message);
	echo json_encode( $oMessage );
}
?>
