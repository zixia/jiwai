<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo 	= JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$statusData = JWRemote::GetFriendStatus($loginedIdUser);
$pagination = new JWPagination(count($statusData['status_ids']), $page, 10);
$statusData = JWUtility::SliceStatusData($statusData, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$statusRows = JWDB_Cache_Status::GetDbRowsByIds($statusData['status_ids']);
$userRows = JWDB_Cache_User::GetDbRowsByIds($statusData['user_ids']);

krsort( $statusRows );

$statuses = array();
foreach( $statusRows as $k=>$s){

$protected = JWSns::IsProtected( $userRows[$s['idUser']], $loginedIdUser ) 
                || JWSns::IsProtectedStatus( $s, $loginedIdUser );

	if($protected) continue;
    $fs = JWStatus::FormatStatus( $s, false, false, true );
    $s['status'] = $fs['status'];
    $statuses[ $k ] = $s;
}

$followingsNum = JWFollower::GetFollowingNum( $loginedUserInfo['id'] );
$followersNum = JWFollower::GetFollowerNum( $loginedUserInfo['id'] );

$shortcut = array( 'public_timeline', 'logout', 'my', 'search', 'favourite', 'message' , 'followings', 'index', 'replies' );
$pageString = paginate( $pagination, '/wo/' );
JWRender::Display( 'wo/archive', array(
    'loginedUserInfo' => $loginedUserInfo,
    'users' => $userRows,
    'statuses' => $statuses,
    'followingsNum' => $followingsNum,
    'followersNum' => $followersNum,
    'shortcut' => $shortcut,
    'pageString' => $pageString,
));
?>
