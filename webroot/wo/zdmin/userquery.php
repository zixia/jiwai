<?php
require_once( dirname(__FILE__) . '/function.php');

$un = null;
$im = null;
extract($_GET, EXTR_IF_EXISTS);

// Device
$unResult = array();
$imResult = array();
if( $un ) {
	$oneResult = JWUser::GetUserInfo( $un );
	if( $oneResult ) {
		array_push( $unResult, $oneResult );
		$imResult = JWDevice::GetDeviceRowByUserId( $oneResult['id'] );
	}
}

//Status
$stResult = array();
if( !empty($unResult) ){
	$statusIds = JWStatus::GetStatusIdsFromUser($unResult[0]['id'],10);
	$stResult = JWStatus::GetDbRowsByIds( $statusIds['status_ids'] );
	krsort( $stResult );
}

//Friends
if( !empty($unResult) ){
	$unResult[0]['numStatus'] = JWStatus::GetStatusNum( $unResult[0]['id'] );
	$unResult[0]['numFriend'] = JWFollower::GetFollowingNum( $unResult[0]['id'] );
	$unResult[0]['numFollower'] = JWFollower::GetFollowerNum( $unResult[0]['id'] );
	$unResult[0]['numMessage'] = JWMessage::GetMessageNum( $unResult[0]['id'], JWMessage::INBOX );
	$unResult[0]['numFavourite'] = JWFavourite::GetFavouriteNum( $unResult[0]['id'] );
}


$render = new JWHtmlRender();
$render->display("userquery", array(
			'menu_nav' => 'userquery',
			'un' => $un,
			'im' => $im,
			'unResult' => $unResult,
			'imResult' => $imResult,
			'stResult' => $stResult,
			));
?>
