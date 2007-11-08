<?php
$friendsNum = JWFriend::GetFriendNum( $userInfo['id'] );
$pagination = new JWPagination( $friendsNum, $page, 10 );
$friendIds  = JWFriend::GetFriendIds( $userInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$friendRows = JWUser::GetUserDbRowsByIds($friendIds);

$pageTitle = htmlSpecialChars($userInfo['nameFull'])."关注的人";

$pageString = paginate( $pagination, '/'.$userInfo['nameUrl'].'/friends/' );

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','friends','message', 'replies');
}

JWRender::Display( 'user/friends', array(
                'friends' => $friendRows,
                'userInfo' => $userInfo,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
