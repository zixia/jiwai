<p><img width="64" height="64" src="${JWPicture::GetUserIconUrl($userInfo['id'],'thumb96')}" alt="{$userInfo['nameScreen']}"/></p>
<!--${$op = friendsop( $loginedUserInfo['id'], $userInfo['id'] ) }-->
${$op ? "好友操作：$op" : ''}
