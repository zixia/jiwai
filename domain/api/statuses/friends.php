<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400,true);
}

$authed = false;
@list($id, $type) = explode( ".", $pathParam, 2);
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}
if( !$id ) {
	$idUser = JWApi::GetAuthedUserId();
	if( !$idUser ){
		JWApi::RenderAuth(JWApi::AUTH_HTTP);
	}
	$authed = true;
}else{
	$_cUser = JWUser::GetUserInfo( $id );
	if( !$_cUser ){
		JWApi::OutHeader(404, true);
	}
	$idUser = $_cUser['id'];
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
	$friendsWithStatus = getFriendsWithStatus( $idUser );
	echo json_encode( $friendsWithStatus );
}

function renderXmlStatuses($idUser){
	$friendsWithStatus = getFriendsWithStatus( $idUser );
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $friendsWithStatus, 1, "users" );
	echo $xmlString;
}

function getFriendsWithStatus($idUser){
	$friendIds = JWFollower::GetFollowingIds($idUser);
	$friends = JWDB_Cache_User::GetDbRowsByIds( $friendIds );
	$statusIds = array();
	foreach( $friendIds as $f ){
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		if( false == empty( $_rs ) ) {
			$statusIds[$f] = $_rs['status_ids'][0];
		}
	}
	$statuses = JWStatus::GetDbRowsByIds( array_values($statusIds) );
	
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
