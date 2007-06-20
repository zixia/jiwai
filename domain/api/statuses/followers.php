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

$idUser = JWApi::GetAuthedUserId();
if( !$idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

switch( $type ){
	case 'json':
		renderJsonStatuses($idUser);
	break;
	case 'xml':
		renderXmlStatuses($idUser);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderJsonStatuses($idUser){
	$followersWithStatus = getFollowersWithStatus( $idUser );
	echo json_encode( $followersWithStatus );
}

function renderXmlStatuses($idUser){
	$followersWithStatus = getFollowersWithStatus( $idUser );
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $followersWithStatus, 1, "users" );
	echo $xmlString;
}

function getFollowersWithStatus($idUser){
	$followerIds = JWFollower::GetFollowerIds($idUser);
	$followers = JWUser::GetUserDbRowsByIds( $followerIds );
	$statusIds = array();
	foreach( $followerIds as $f ){
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		$statusIds[$f] = $_rs['status_ids'][0];
	}
	$statuses = JWStatus::GetStatusDbRowsByIds( array_values($statusIds) );
	
	$followersWithStatuses = array();
	foreach($followerIds as $f ){
		$user = $followers[$f];
		$userInfo = JWApi::ReBuildUser( $user );
		$status = $statuses[ $statusIds[$f] ];
		$statusInfo = JWApi::ReBuildStatus( $status );
		$userInfo['status'] = $statusInfo;

		$followersWithStatuses[] = $userInfo;
	}
	return $followersWithStatuses;
}
?>
