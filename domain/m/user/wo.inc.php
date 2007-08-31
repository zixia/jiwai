<?php
$showProtected = $userInfo['isProtected'] == 'N';
if( false == $showProtected ){
    $showProtected = JWFriend::IsFriend( $userInfo['id'], $loginedUserInfo['id'] ) 
                        | $loginedUserInfo['id'] == $userInfo['id'] ;
}

if( $statusTab == 'with_friends' ) {

    $pageTitle = "$userInfo[nameScreen]和朋友们在做什么";

    $statusNum = JWDB_Cache_Status::GetStatusNumFromFriends( $userInfo['id'] );
    $pagination = new JWPagination( $statusNum, $page , 10);
    $statusData =  JWDB_Cache_Status::GetStatusIdsFromFriends( $userInfo['id'] , $pagination->GetNumPerPage(), $pagination->GetStartPos() );
    $statusRows    =  JWDB_Cache_Status::GetDbRowsByIds( $statusData['status_ids']);
    $userRows   =   JWUser::GetUserDbRowsByIds( $statusData['user_ids']);
    $tpl = 'user/with_friends';
}else{

    $pageTitle = "$userInfo[nameScreen]在做什么";

    $statusNum = JWDB_Cache_Status::GetStatusNum( $userInfo['id'] );
    $pagination = new JWPagination( $statusNum, $page , 10);
    $statusData =  JWDB_Cache_Status::GetStatusIdsFromUser( $userInfo['id'] , $pagination->GetNumPerPage(), $pagination->GetStartPos() );
    $statusRows    =  JWDB_Cache_Status::GetDbRowsByIds( $statusData['status_ids']);
    $userRows = array();
    $tpl = 'user/owner';
}

$statuses = array();
foreach( $statusRows as $k=>$s ){
    $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$shortcut = array('public_timeline', 'index');
if( JWLogin::isLogined() ) {
    array_push( $shortcut, 'logout', 'my', 'message', 'friends' );
}else{
    array_push( $shortcut, 'register' );
}

$url = "/$userInfo[nameScreen]/" . ( $statusTab=='with_friends' ? 'with_friends/' : '' );
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
