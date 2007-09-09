<p><img src="${JWPicture::GetUserIconUrl($userInfo['id'],'thumb48')}" width="48" height="48" alt="{$userInfo['nameScreen']}"/></p>
<!--${$op = friendsop( $loginedUserInfo['id'], $userInfo['id'] ) }-->
${$op ? "好友操作：$op" : ''}
