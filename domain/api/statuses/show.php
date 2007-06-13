<?php
require_once("../../../jiwai.inc.php");
require_once(dirname(__FILE__).'/arrayxml.php');

$pathParam = null;
$id = null;
$type = null;
# by zixia: add EXTR_IF_EXISTS
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	exit;
}

@list($id, $type) = explode( ".", $pathParam, 2);

if( is_numeric($id) ){
	switch( $type ){
	case 'json':
		renderJsonStatuses($id);
	break;
	case 'xml':
		renderXmlStatuses($id);
	break;
	default:
		exit;
	}
}

function renderJsonStatuses($id){
	$status = $user = null;
	if( getMessage( $id, $status, $user )){
		$outInfo = buildOutputArray($status,$user);
		echo json_encode( $outInfo );
	}
}

function renderXmlStatuses($id){
	$status = $user = $xmlString = null;
	if( getMessage( $id, $status, $user )){
		$outInfo = buildOutputArray($status,$user);
		header('Content-Type: application/xml; charset=utf-8');
		$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xmlString .= "<status>\n";
		$xmlString .= array_to_xml( $outInfo );
		$xmlString .= "</status>\n";
		echo $xmlString;
	}
}

function buildOutputArray($status,$user){

		$outInfo = array();
		$outInfo['create_at'] = date("D M d H:i:s O Y",$status['timeCreate']);
		$outInfo['text'] = $status['status'];
		$outInfo['id'] = $status['idStatus'];
		
		//uInfo
		$uInfo = array();
		$uInfo['name'] = $user['nameFull'];
		$uInfo['description'] = $user['bio'];
		$uInfo['location'] = $user['location'];
		$uInfo['url'] = $user['url'];
		$uInfo['id'] = $user['id'];
		$uInfo['protected'] = $user['protected']=='Y' ? true:false;
		$uInfo['profile_image_url'] = $user['idPicture'];
		$uInfo['screen_name'] = $user['nameScreen'];

		$outInfo['user'] = $uInfo;

		return $outInfo;
}

function getMessage($id, &$status, &$user){
	$status = JWStatus::getStatusDbRowById($id);
	if( $status ){
		$user = JWUser::getUserDbRowById($status['idUser']);
		return true;
	}
	return false;
}
?>
