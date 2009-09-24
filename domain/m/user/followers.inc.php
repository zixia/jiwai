<?php
$followersNum = JWFollower::GetFollowerNum( $userInfo['id'] );
$pagination = new JWPagination( $followersNum, $page, 10 );
$followerIds  = JWFollower::GetFollowerIds( $userInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$followerRows = JWDB_Cache_User::GetDbRowsByIds($followerIds);

$pageString = paginate( $pagination, '/'.urlencode($userInfo['nameUrl']).'/followers/' );

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','favourite', 'search', 'followings','message','replies');
}

$pageTitle = "关注此人的人";

JWRender::Display( 'user/followers', array(
                'followers' => $followerRows,
                'userInfo' => $userInfo,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
