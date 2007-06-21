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
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( !$idStatus || !is_numeric($idStatus) ) {
	JWApi::OutHeader(400, true);
}
$unStatus = JWStatus::GetStatusDbRowById( $idStatus );
if( !$unStatus ) {
	JWApi::OutHeader(404, true);
}

/**
  * status's owner check 
  */
if( $unStatus['idUser'] != $idUser ) {
	JWApi::OutHeader(403, true);
}

/**
  * Destroy direct status by Id
  */
if( JWStatus::Destroy($idStatus) ){
	switch($type){
		case 'xml':
			renderXmlReturn($unStatus);
		break;
		case 'json':
			renderJsonReturn($unStatus);
		break;
		default:
			JWApi::OutHeader(406, true);	
	}	
}else{
	JWApi::OutHeader(500, true);
}

function renderXmlReturn($status){
	$oStatus = JWApi::RebuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;
	
	$xmlString = null;
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
?>
