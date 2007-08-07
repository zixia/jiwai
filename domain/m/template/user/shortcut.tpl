<p><img src="${JWPicture::GetUserIconUrl($userInfo['id'],'thumb48')}" alt="{$userInfo['nameScreen']}"/></p>
<!--${$op = friendsop( $loginedUserInfo['id'], $userInfo['id'] ) }-->
${$op ? "好友操作：$op" : ''}
