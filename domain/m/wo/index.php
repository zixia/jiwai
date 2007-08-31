<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo 	= JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;


$statusNum= JWStatus::GetStatusNumFromFriends($loginedIdUser);
$pagination		= new JWPagination($statusNum, $page, 10);
$statusData 	= JWStatus::GetStatusIdsFromFriends($loginedIdUser, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$statusRows	= JWDB_Cache_Status::GetDbRowsByIds($statusData['status_ids']);
$userRows		= JWUser::GetUserDbRowsByIds	($statusData['user_ids']);

krsort( $statusRows );

$statuses = array();
foreach( $statusRows as $k=>$s){
    $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$friendsNum = JWFriend::GetFriendNum( $loginedUserInfo['id'] );
$followersNum = JWFollower::GetFollowerNum( $loginedUserInfo['id'] );

$shortcut = array( 'public_timeline', 'logout', 'my', 'message' , 'friends' );
$pageString = paginate( $pagination, '/wo/' );
JWRender::Display( 'wo/archive', array(
    'loginedUserInfo' => $loginedUserInfo,
    'users' => $userRows,
    'statuses' => $statuses,
    'friendsNum' => $friendsNum,
    'followersNum' => $followersNum,
    'shortcut' => $shortcut,
    'pageString' => $pageString,
));
?>
