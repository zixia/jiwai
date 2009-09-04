<!--{include header}-->
<!--{include wo/update}-->
<!--${
	$msgCount = JWMessage::GetMessageStatusNum($loginedUserInfo['id'], JWMessage::INBOX, JWMessage::MESSAGE_NOTREAD);
	$msgString = ( $msgCount == 0 ) ? '' : '('.$msgCount.'条)';
}-->
<h2><a href="/wo/">最新</a>｜<a href="/wo/replies/">回复</a>｜<a href="/wo/message/inbox">悄悄话{$msgString}</a> | 头像</h2>
<p><a href="/${HtmlSpecialChars($loginedUserInfo['nameUrl'])}/"><img src="${JWPicture::GetUserIconUrl($loginedUserInfo['id'],'thumb48s')}" width="48" height="48" border="0" alt="{$loginedUserInfo['nameScreen']}"/></a></p>
<ul>
<form action="/wo/avatar" method="post" enctype="multipart/form-data">
	<p><input type="file" name="profile_image" /></p>
	<p><input type="submit" value="更新头像"/></p>
</form>
</ul>

<!--{include shortcut}-->
<!--{include footer}-->
