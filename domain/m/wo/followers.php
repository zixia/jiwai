<?php
require_once( '../config.inc.php' );

$page = 1;
extract( $_REQUEST, EXTR_IF_EXISTS );

JWLogin::MustLogined();

$pageTitle = "关注你的人";

$loginedUserInfo = JWUser::GetCurrentUserInfo();

$followersNum = JWFollower::GetFollowerNum( $loginedUserInfo['id'] );
$pagination = new JWPagination( $followersNum, $page, 10 );
$followerIds = JWFollower::GetFollowerIds($loginedUserInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos());
$followerRows = JWDB_Cache_User::GetDbRowsByIds($followerIds);

$followerOps = actionop( $loginedUserInfo['id'], $followerIds , $forFollow = true);

$pageString = paginate( $pagination, '/wo/followers/' );

$shortcut = array( 'my', 'index', 'logout', 'public_timeline', 'search', 'favourite', 'message', 'followings', 'replies' );
JWRender::Display( 'wo/followers', array(
                'followers' => $followerRows,
                'followerOps' => $followerOps,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
