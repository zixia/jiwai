<?php
require_once("../../../jiwai.inc.php");
error_reporting(E_ALL);

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	exit;
}

@list($nameOrId, $type) = explode( ".", $pathParam, 2);

$idUser = JWApi::GetAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$unFriendId = $nameOrId;
$unFriendUser = null;
if(is_string($nameOrId)){
	$unFriendUser = JWUser::GetUserInfo( $nameOrId, null );
	$unFriendId = $unFriendUser['id'];
}

if( !$unFriendId ){
	Header("HTTP/1.0 404 Not Found");
	exit;
}

//FriendShip Check, If no friend relation, return 403
if( false === JWFriend::IsFriend($idUser, $unFriendId) ){
	Header("Status: 403 Access Denied");
	exit;
}

//Destroy the friendship of idUser & unFriendId
JWFriend::Destroy($idUser, $unFriendId);

switch( $type ){
	case 'json':
		renderJsonReturn($unFriendUser);
	break;
	case 'xml':
		renderXmlReturn($unFriendUser);
	break;
	default:
	exit;
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

	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $userInfo, 0, 'user' );
	echo $xmlString;
}
?>
