<?php
require_once('../../../jiwai.inc.php');
if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	header_405();
}

$user = $text = null;
extract($_POST, EXTR_IF_EXISTS);
$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !$user || !$text ) {
	header_400();
}

$idUserSender = JWApi::GetAuthedUserId();
if( ! $idUserSender ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}
$device = 'web';
$timeCreate = date("Y-m-d H:i:s");
$idUserReceiver = JWUser::GetUserInfo( $user, 'id' );
$text = urlDecode( $text );
if( !$idUserReceiver ){
	header_400();
}

if(JWMessage::Create($idUserSender, $idUserReceiver, $text, $device, $time=null)){
	$insertedId = JWDB::GetInsertedId();
	$message = JWMessage::GetMessageDbRowById( $insertedId );
	switch($type){
		case 'xml':
			renderXmlReturn($message);
		break;
		default:
			renderJsonReturn($message);
	}	
}else{
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
function header_400(){
	Header("HTTP/1.1 400 Bad Request");
	exit;
}
?>
