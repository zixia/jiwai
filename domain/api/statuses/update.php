<?php
require_once('../../../jiwai.inc.php');
if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	JWApi::OutHeader(405,true);
}

$status = null;
$idPartner = null;
extract($_POST, EXTR_IF_EXISTS);
$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( !$status ) {
	JWApi::OutHeader(400,true);
}
$status = mb_convert_encoding( $status, "UTF-8", "GB2312,UTF-8");

if( ! $idUser=JWApi::GetAuthedUserId() ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$device = 'api';
$timeCreate = date("Y-m-d H:i:s");
$status = urlDecode( $status );
$isSignature = 'N';
$serverAddress = null;
$options = array(
                'idPartner' => $idPartner,
            );

if( $insertedId = JWSns::UpdateStatus($idUser, $status, $device, $time=null, $isSignature, $serverAddress, $options) ){
	if( $insertedId === true ) {
		$insertedId = JWDB::GetInsertedId();
	}
	$status = JWDB_Cache_Status::GetDbRowById( $insertedId );
	if (empty($status))
		JWApi::OutHeader(406, true);

	switch($type){
		case 'xml':
			renderXmlReturn($status);
		break;
		case 'json':
			renderJsonReturn($status);
		break;
		default:
			JWApi::OutHeader(406, true);
	}	
}else{
	JWApi::OutHeader(500, true);
}

function renderXmlReturn($status)
{
	$oStatus = JWApi::ReBuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $oStatus, 0, 'status' );
	echo $xmlString;
}

function renderJsonReturn($status)
{
	$oStatus = JWApi::RebuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;

	echo json_encode( $oStatus );
}
?>
