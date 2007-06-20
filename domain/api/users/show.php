<?php
require_once("../../../jiwai.inc.php");
error_reporting(E_ALL);

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	exit;
}
@list($idUserOrName, $type) = explode( ".", $pathParam, 2);

/** This API need Authed User, no matter show self/other's extends info. */
$idAuthedUser = JWApi::GetAuthedUserId();
if( !$idAuthedUser ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}
/** end auth*/

if( !$idUserOrName ){
	$idUserOrName = $idAuthedUser;
}

$user = JWUser::GetUserInfo( $idUserOrName );
if( !$user ){
	header_404();
}

switch( $type ){
	case 'xml':
		renderXmlReturn($user);
	break;
	case 'json':
		renderJsonRentru($user);
	break;
	default:
	break;
}

function renderXmlReturn($user){
	$userInfo = getUserExtendWithStatus( $user );
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $userInfo, 0 , 'user');
	echo $xmlString;
	
}

function renderJsonReturn($user){
	$userInfo = getUserExtendWithStatus( $user );
	echo json_encode( $userInfo );
}


function getUserExtendWithStatus($user){
	$idUser = $user['id'];
	
	$userInfo = JWApi::ReBuildUser( $user );
	
	//Desing Info, Come from JWDesign, table:Design
	$design = new JWDesign( $idUser );
	$color = null;
	$design->GetBackgroundColor($color);
	$userInfo['profile_background_color'] = $color;
	$design->GetTextColor($color);
	$userInfo['profile_text_color'] = $color;
	$design->GetLinkColor($color);
	$userInfo['profile_link_color'] = $color;
	$design->GetSidebarFillColor($color);
	$userInfo['profile_sidebar_fill_color'] = $color;
	$design->GetSidebarBorderColor($color);
	$userInfo['profile_sidebar_border_color'] = $color;
	//Count infomation
	/*
	<friends_count>16</friends_count>
		<followers_count>5</followers_count>
		<favourites_count>21</favourites_count>
		<utc_offset>28800</utc_offset>
		<statuses_count>168</statuses_count>
	*/
	$userInfo['friends_count'] = JWFriend::GetFriendNum($idUser);
	$userInfo['followers_count'] = JWFollower::GetFollowerNum($idUser);
	$userInfo['favourite_count'] = JWFavourite::GetFavouriteNum( $idUser );
	$userInfo['utc_offset'] = 28800;
	$userInfo['statuses_count'] = JWStatus::GetStatusNum( $idUser );

	
	//Get Current Stuats,
	$statusIds = JWStatus::GetStatusIdsFromUser( $idUser, 1 );
	$statusId = $statusIds['status_ids'][0];
	$status = JWStatus::GetStatusDbRowById( $statusId );	
	$statusInfo = JWApi::ReBuildStatus( $status );
	
	$userInfo['status'] = $statusInfo;

	return $userInfo;
}

function header_404(){
	Header("HTTP/1.1 404 Not Found");
	exit;
}

?>