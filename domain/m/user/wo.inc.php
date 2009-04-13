<?php
$showProtected = $userInfo['protected'] == 'N' || JWUser::IsAdmin($loginedUserInfo['id']);
if( false == $showProtected ){
	$showProtected = JWFollower::IsFollower($loginedUserInfo['id'], $userInfo['id']) | $loginedUserInfo['id'] == $userInfo['id'] ;
}

if( $statusTab == 'with_friends' ) {

	$pageTitle = htmlSpecialChars($userInfo['nameScreen'])."和别人在做什么";

	$statusNum = JWDB_Cache_Status::GetStatusNumFromFriends( $userInfo['id'] );
	$pagination = new JWPagination( $statusNum, $page , 10);
	$statusData = JWDB_Cache_Status::GetStatusIdsFromFriends( $userInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );

	$statusRows = JWDB_Cache_Status::GetDbRowsByIds( $statusData['status_ids']);
	$userRows = JWDB_Cache_User::GetDbRowsByIds( $statusData['user_ids']);
	$tpl = 'user/with_friends';
}else{

	$pageTitle = htmlSpecialChars($userInfo['nameScreen'])."在做什么";
	
	if( $userInfo['idConference'] ) {
		$statusNum = JWStatus::GetStatusNumFromConference( $userInfo['idConference'] );
		$pagination = new JWPagination( $statusNum, $page, 10);
		$statusData = JWStatus::GetStatusIdsFromConferenceUser( $userInfo['id'],  $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$userRows = JWDB_Cache_User::GetDbRowsByIds( $statusData['user_ids']);
	}else{
		$statusNum = JWDB_Cache_Status::GetStatusNum( $userInfo['id'] );
		$pagination = new JWPagination( $statusNum, $page , 10);
		$statusData = JWDB_Cache_Status::GetStatusIdsFromUser( $userInfo['id'] , $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$userRows = array();
	}

	$statusRows = JWDB_Cache_Status::GetDbRowsByIds( $statusData['status_ids']);
	$tpl = 'user/owner';
}

krsort( $statusRows );
$statuses = array();
foreach( $statusRows as $k=>$s ){
	$fs = JWStatus::FormatStatus( $s, false, false, true );
	$s['status'] = $fs['status'];
	$statuses[ $k ] = $s;
}

$shortcut = array('public_timeline', 'index');
if( JWLogin::isLogined() ) {
    array_push( $shortcut, 'logout','my','favourite', 'search', 'followings','message','replies');
}else{
	array_push( $shortcut, 'register' );
}

$url = "/$userInfo[nameUrl]/" . ( $statusTab=='with_friends' ? 'with_friends/' : '' );
$pageString = paginate( $pagination, $url );
JWRender::Display( $tpl , array(
	'userInfo' => $userInfo,
	'statuses' => $statuses,
	'users' => $userRows,
	'shortcut' => $shortcut,
	'showProtected' => $showProtected,
	'loginedUserInfo' => $loginedUserInfo,
	'pageString' => $pageString,
));
?>
