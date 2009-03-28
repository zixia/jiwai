<?php
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
    $fs = JWStatus::FormatStatus( $s, false, false, true );
    $s['status'] = $fs['status'];
   // $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}
$pageString = paginate( $pagination, "/t/$tag_row[name]/" );

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','favourite', 'search', 'followings','message','replies');
}
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
