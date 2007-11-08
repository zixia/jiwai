<?php
$followersNum = JWFollower::GetFollowerNum( $userInfo['id'] );
$pagination = new JWPagination( $followersNum, $page, 10 );
$followerIds  = JWFollower::GetFollowerIds( $userInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$followerRows = JWUser::GetUserDbRowsByIds($followerIds);

$pageString = paginate( $pagination, '/'.$userInfo['nameUrl'].'/followers/' );

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','friends','message','replies');
}

$pageTitle = "关注 ".htmlSpecialChars($userInfo['nameFull'])." 的人";

JWRender::Display( 'user/followers', array(
                'followers' => $followerRows,
                'userInfo' => $userInfo,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
