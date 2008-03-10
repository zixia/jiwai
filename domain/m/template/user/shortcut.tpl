<p><a href="/${HtmlSpecialChars($userInfo['nameUrl'])}/"><img src="${JWPicture::GetUserIconUrl($userInfo['id'],'thumb48s')}" width="48" height="48" border="0" alt="{$userInfo['nameScreen']}"/></a></p>
<!--${$op = actionop( $loginedUserInfo['id'], $userInfo['id'] ) }-->
${$op ? "关注操作：$op" : ''}
