<?php
require_once('../../../jiwai.inc.php');
if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	JWApi::OutHeader(405, true);
}

$user = $text = null;
extract($_POST, EXTR_IF_EXISTS);
$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;


$type = trim( strtolower($pathParam), '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( false == ( $user && $text ) ) {
	JWApi::OutHeader(400, true);
}

$idUserSender = JWApi::GetAuthedUserId();
if( ! $idUserSender ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$device = 'web';
$timeCreate = date("Y-m-d H:i:s");
$userReceiver = JWUser::GetUserInfo( $user );
if( !$userReceiver ){
	JWApi::OutHeader(404, true);
}
$idUserReceiver = $userReceiver['id'];

// Check Friend Relation, idUserSender must be idUserSender's friend;
if( false == JWFollower::IsFollower($idUserSender, $idUserReceiver) ){
	JWApi::OutHeader(403, true);
}

$text = urlDecode( $text );
if(JWMessage::Create($idUserSender, $idUserReceiver, $text, $device, $time=null)){
	$insertedId = JWDB::GetInsertedId();
	$message = JWMessage::GetMessageDbRowById( $insertedId );
	switch($type){
		case 'xml':
			renderXmlReturn($message);
		break;
		case 'json':
			renderJsonReturn($message);
		break;
		default:
			JWApi::OutHeader(406, true);
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
