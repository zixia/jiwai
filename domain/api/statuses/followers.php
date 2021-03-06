<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400, true);
}

$authed = false;
$bound = strrpos($pathParam, '.');
if ($bound === false) {
	JWApi::OutHeader(406, true);
} else {
    $id = substr($pathParam, 0, $bound);
    $type = substr($pathParam, $bound + 1);
}
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
    ob_start();
    ob_start("ob_gzhandler");
	$followersWithStatus = getFollowersWithStatus( $idUser );
	echo json_encode( $followersWithStatus );
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderXmlStatuses($idUser){
    ob_start();
    ob_start("ob_gzhandler");
	$followersWithStatus = getFollowersWithStatus( $idUser );

	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $followersWithStatus, 1, "users" );
	echo $xmlString;
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function getFollowersWithStatus($idUser)
{
	$followerIds = JWFollower::GetFollowerIds($idUser);
	$followers = JWDB_Cache_User::GetDbRowsByIds( $followerIds );
	$statusIds = array();
	foreach( $followerIds as $f )
	{
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		$statusIds[$f] = $_rs['status_ids'][0];
	}
	$statuses = JWStatus::GetDbRowsByIds( array_values($statusIds) );
	
	$followersWithStatuses = array();
	foreach($followerIds as $f )
	{
		$user_row = $followers[$f];
		$status_row = $statuses[ $statusIds[$f] ];
		$user_row['idPicture'] = ( $status_row['idPicture'] && 'MMS' != $status_row['statusType'] )
			? $status_row['idPicture'] : $user_row['idPicture'];

		$userInfo = JWApi::ReBuildUser( $user_row );

		$statusInfo = JWApi::ReBuildStatus( $status_row );
		$userInfo['status'] = $statusInfo;

		$followersWithStatuses[] = $userInfo;
	}
	return $followersWithStatuses;
}
?>
