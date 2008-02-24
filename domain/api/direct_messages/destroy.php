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
$message_id = $type = null;
@list($message_id, $type)= explode('.', trim( $pathParam, '/' ));
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( !$message_id ) {
	JWApi::OutHeader(400, true);
}
$un_message = JWMessage::GetMessageDbRowById( $message_id );
if( !$un_message ) {
	JWApi::OutHeader(404, true);
}

/**
  * message's owner check 
  */
if( $un_message['idUserReceiver'] != $idUser
	&& $un_message['idUserSender'] != $idUser ) 
{
	JWApi::OutHeader(403, true);
}

/**
  * Destroy direct message by Id
  */
$flag = true;
if ( $un_message['idUserSender'] == $idUser )
{
	$flag &= JWMessage::SetMessageStatus( $message_id, JWMessage::OUTBOX, JWMessage::MESSAGE_DELETE );
}
if ( $un_message['idUserReceiver'] == $idUser )
{
	$flag &= JWMessage::SetMessageStatus( $message_id, JWMessage::INBOX, JWMessage::MESSAGE_DELETE );
}

/**
 * out infomation
 */
if( $flag )
{
	switch($type){
		case 'xml':
			renderXmlReturn($un_message);
		break;
		case 'json':
			renderJsonReturn($un_message);
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
