<?php
$pageTitle = "叽歪广场";

$status_data    = JWStatus::GetStatusIdsFromPublic(100);
$status_rows    = JWStatus::GetDbRowsByIds($status_data['status_ids']);
$user_rows      = JWDB_Cache_User::GetDbRowsByIds    ($status_data['user_ids']);

krsort( $status_rows );

$userIds = array();
$statuses = array();
$maxStatus = 10;
foreach( $status_rows as $s){
    if( in_array( $s['idUser'], $userIds ) )
        continue;

    $fs = JWStatus::FormatStatus( $s, false );
    $s['status'] = $fs['status'];

    array_push( $userIds, $s['idUser'] );
    array_push( $statuses, $s );

    if( count( $statuses ) >= $maxStatus )
        break;
}

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','followings','message', 'replies');
}
JWRender::Display( 'public_timeline', array(
    'users' => $user_rows,
    'statuses' => $statuses,
    'loginedUserInfo' => $loginedUserInfo,
    'userInfo' => $userInfo,
    'shortcut' => $shortcut,
));
?>
