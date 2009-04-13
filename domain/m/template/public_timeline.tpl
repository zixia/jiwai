<!--{include header}-->
<!--{include wo/update}-->

<h2>叽歪广场｜<a href="/t/">热门标签</a></h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
    <a href="${buildUrl('/'.htmlSpecialChars($users[$status['idUser']]['nameUrl']).'/')}" rel="contact">${getDisplayName($users[$status['idUser']])}</a>：{$status['status']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($status['timeCreate'])}
    通过
    ${JWDevice::GetNameFromType($status['device'], $status['idPartner'])}${$status['statusType'] == 'SIG' ? '签名' : ''}
    </span>
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/message/create/".$status['idUser']."\">悄悄话</a>" : ''}
	${($loginedUserInfo['id'] && false==JWFavourite::IsFavourite($loginedUserInfo['id'],$status['id'])) ? "<a href=\"/wo/status/favourite/".$status['id']."\">收藏</a>" : "<a href=\"/wo/status/unfavourite/".$status['idUser']."\">取消收藏</a>"}
    <a href="/wo/status/r/{$status['id']}">回复</a>
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/status/rt/".$status['id']."\">RT</a>" : ''}
</li>
<!--{/foreach}-->
</ul>
<!--{include wo/update}-->

<!--{include shortcut}-->
<!--{include footer}-->
