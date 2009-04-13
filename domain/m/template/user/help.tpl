<!--{include header}-->
<h2>有什么要对叽歪de说的？</h2>
<form action="/wo/status/update" method="post">
<p><input type="text" name="status"/></p>
<p><input type="submit" value="留言"/></p>
</form>
<h2>叽歪de留言版</h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
	<a href="${buildUrl('/'.$users[$status['idUser']]['nameUrl'].'/')}">${getDisplayName($users[$status['idUser']])}</a>：{$status['status']}
	<span class="stamp">
	${JWStatus::GetTimeDesc($status['timeCreate'])}
	通过
	${JWDevice::GetNameFromType($status['device'])}${$status['statusType'] == 'SIG' ? '签名' : ''}
	</span>
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/message/create/".$status['idUser']."\">悄悄话</a>" : ''}
	${($loginedUserInfo['id'] && false==JWFavourite::IsFavourite($loginedUserInfo['id'],$status['id'])) ? "<a href=\"/wo/status/favourite/".$status['id']."\">收藏</a>" : "<a href=\"/wo/status/unfavourite/".$status['idUser']."\">取消收藏</a>"}
    <a href="/wo/status/r/{$status['id']}">回复</a>
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/status/rt/".$status['id']."\">RT</a>" : ''}
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
