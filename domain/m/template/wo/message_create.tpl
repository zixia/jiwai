<!--{include header}-->

<h2>给 <a href="/{$userInfo['nameScreen']}/">${htmlSpecialChars($userInfo['nameFull'])}</a> 发送悄悄话</h2>
<form method="post" action="/wo/message/send/{$userInfo['id']}">
<p><textarea name="content" rows="3" cols="30"></textarea></p>
<input type="submit" value="发送" /></p>
</form>

<!--{include shortcut}-->
<!--{include footer}-->
