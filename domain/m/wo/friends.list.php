<?php
$pageTitle = "我的朋友们";

$friendsNum = JWFriend::GetFriendNum( $loginedUserInfo['id'] );
$pagination = new JWPagination( $friendsNum, $page, 10 );
$friendIds  = JWFriend::GetFriendIds( $loginedUserInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$friendRows = JWUser::GetUserDbRowsByIds($friendIds);

$friendOps = friendsop( $loginedUserInfo['id'], $friendIds );

$pageString = paginate( $pagination, '/wo/friends/' );

$shortcut = array( 'my', 'index', 'logout', 'public_timeline', 'message' );
JWRender::Display( 'wo/friends', array(
                'friends' => $friendRows,
                'friendOps' => $friendOps,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
