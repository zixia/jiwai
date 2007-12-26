<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo = JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$statusNum	= JWDB_Cache_Status::GetCountTopicByIdTag($tag_id);
$pagination	= new JWPagination($statusNum, $page, 10);
$statusData 	= JWDB_Cache_Status::GetStatusIdsTopicByIdTag($tag_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$statusRows	= JWDB_Cache_Status::GetDbRowsByIds($statusData['status_ids']);
$userRows	= JWDB_Cache_User::GetDbRowsByIds	($statusData['user_ids']);

krsort( $statusRows );

$statuses = array();
foreach( $statusRows as $k=>$s){
    $fs = JWStatus::FormatStatus( $s, false );
    $s['status'] = $fs['status'];
   // $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$followingsNum = JWFollower::GetFollowingNum( $loginedUserInfo['id'] );
$followersNum = JWFollower::GetFollowerNum( $loginedUserInfo['id'] );

$shortcut = array( 'public_timeline', 'logout', 'my', 'message' , 'followings', 'index', 'replies');
$pageString = paginate( $pagination, "/t/$tag_row[name]/" );
JWRender::Display( 't/channel', array(
    'loginedUserInfo' => $loginedUserInfo,
    'users' => $userRows,
    'statuses' => $statuses,
    'followingsNum' => $followingsNum,
    'followersNum' => $followersNum,
    'shortcut' => $shortcut,
    'pageString' => $pageString,
	'tag_row' => $tag_row,
));
?>
