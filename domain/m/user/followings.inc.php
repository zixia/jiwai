<?php
$followingsNum = JWFollower::GetFollowingNum( $userInfo['id'] );
$pagination = new JWPagination( $followingsNum, $page, 10 );
$followingIds  = JWFollower::GetFollowingIds( $userInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$followingRows = JWDB_Cache_User::GetDbRowsByIds($followingIds);

$pageTitle = "此人关注的人";

$pageString = paginate( $pagination, '/'.$userInfo['nameUrl'].'/followings/' );

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','favourite', 'search', 'followings','message','replies');
}

JWRender::Display( 'user/followings', array(
                'followings' => $followingRows,
                'userInfo' => $userInfo,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
