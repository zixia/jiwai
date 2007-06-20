<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400, true);
}

$authed = false;
@list($_, $type) = explode( ".", $pathParam, 2);
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

switch( $type ){
	case 'json':
		renderJsonStatuses();
	break;
	case 'xml':
		renderXmlStatuses();
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderJsonStatuses(){
	$featuredWithStatus = getFeaturedWithStatus(  );
	echo json_encode( $featuredWithStatus );
}

function renderXmlStatuses(){
	$featuredWithStatus = getFeaturedWithStatus(  );
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $featuredWithStatus, 1, "users" );
	echo $xmlString;
}

function getFeaturedWithStatus(){
	$featuredIds = JWUser::GetFeaturedUserIds(20);
	$featured = JWUser::GetUserDbRowsByIds( $featuredIds );

	$statusIds = array();
	foreach( $featuredIds as $f ){
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		$statusIds[$f] = $_rs['status_ids'][0];
	}
	$statuses = JWStatus::GetStatusDbRowsByIds( array_values($statusIds) );
	
	$featuredWithStatuses = array();
	foreach($featuredIds as $f ){
		$user = $featured[$f];
		$userInfo = JWApi::ReBuildUser( $user );
		$status = $statuses[ $statusIds[$f] ];
		$statusInfo = JWApi::ReBuildStatus( $status );
		$userInfo['status'] = $statusInfo;

		$featuredWithStatuses[] = $userInfo;
	}
	return $featuredWithStatuses;
}
?>
