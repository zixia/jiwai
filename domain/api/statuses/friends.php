<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	exit;
}

$authed = false;
@list($id, $type) = explode( ".", $pathParam, 2);
if( !$id ) {
	$idUser = JWApi::GetAuthedUserId();
	if( !$idUser ){
		JWApi::RenderAuth(JWApi::AUTH_HTTP);
	}
	$authed = true;
}else{
	$idUser = is_numeric($id) ? intval($id) : JWUser::GetUserInfo($id, 'id');
}

switch( $type ){
	case 'json':
		renderJsonStatuses($idUser);
	break;
	case 'xml':
		renderXmlStatuses($idUser);
	break;
	default:
		exit;
}

function renderJsonStatuses($idUser){
	$friendsWithStatus = getFriendsWithStatus( $idUser );
	echo json_encode( $friendsWithStatus );
}

function renderXmlStatuses($idUser){
	$friendsWithStatus = getFriendsWithStatus( $idUser );
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $friendsWithStatus, 1, "users" );
	echo $xmlString;
}

function getFriendsWithStatus($idUser){
	$friendIds = JWFriend::GetFriendIds($idUser);
	$friends = JWUser::GetUserDbRowsByIds( $friendIds );
	$statusIds = array();
	foreach( $friendIds as $f ){
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		$statusIds[$f] = $_rs['status_ids'][0];
	}
	$statuses = JWStatus::GetStatusDbRowsByIds( array_values($statusIds) );
	
	$friendsWithStatuses = array();
	foreach($friendIds as $f ){
		$user = $friends[$f];
		$userInfo = JWApi::ReBuildUser( $user );
		$status = $statuses[ $statusIds[$f] ];
		$statusInfo = JWApi::ReBuildStatus( $status );
		$userInfo['status'] = $statusInfo;

		$friendsWithStatuses[] = $userInfo;
	}
	return $friendsWithStatuses;
}
?>
