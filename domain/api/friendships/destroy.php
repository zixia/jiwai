<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400, true);
}

@list($nameOrId, $type) = explode( ".", $pathParam, 2);
if( !in_array($type, array('json','xml'))){
	JWApi::OutHeader(406, true);
}

$idUser = JWApi::GetAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$unFriendUser = JWUser::GetUserInfo( $nameOrId, null );
if( ! $unFriendUser ){
	JWApi::OutHeader(404, true);
}
$unFriendId = $unFriendUser['id'];

//FriendShip Check, If no friend relation, return 403
if( false === JWFriend::IsFriend($idUser, $unFriendId) ){
	JWApi::OutHeader(403, true);
}

//Destroy the friendship of idUser & unFriendId
if( false == JWFriend::Destroy($idUser, $unFriendId) ){
	JWApi::OutHeader(500, true);
}

switch( $type ){
	case 'json':
		renderJsonReturn($unFriendUser);
	break;
	case 'xml':
		renderXmlReturn($unFriendUser);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function getUserLastStatus($user){
	$head_status_data = JWStatus::GetStatusIdsFromUser( $user['id'], 1 );
	$head_status_row = JWStatus::GetStatusDbRowById($head_status_data['status_ids'][0]);
	return $head_status_row;
}

function renderJsonReturn($user){
	
	$status = getUserLastStatus($user);
	$statusInfo = JWApi::RebuildStatus($status);

	$userInfo = JWApi::RebuildUser($user);
	$userInfo['status'] = $statusInfo;

	echo json_encode( $userInfo );
}

function renderXmlReturn($user){
	$status = getUserLastStatus($user);
	$statusInfo = JWApi::RebuildStatus($status);

	$userInfo = JWApi::RebuildUser($user);
	$userInfo['status'] = $statusInfo;
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $userInfo, 0, 'user' );
	echo $xmlString;
}
?>
