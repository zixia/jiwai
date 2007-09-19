<?php
require_once('../../../jiwai.inc.php');

$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

$status = mb_convert_encoding( $status, "UTF-8", "GB2312,UTF-8");

$idUser = JWApi::GetAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$result = array( 'authorized' => true, );

switch($type){
	case 'xml':
		renderXmlReturn($result);
	break;
	case 'json':
		renderJsonReturn($result);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($result){
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $result, 0 );
	echo $xmlString;
}

function renderJsonReturn($result){
	echo json_encode( $result );
}
?>
