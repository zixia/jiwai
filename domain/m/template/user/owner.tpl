<!--{include header}-->
<!--{include user/shortcut}-->

<h2>${htmlSpecialChars($userInfo['nameScreen'])}的消息｜<a href="/{$userInfo['nameUrl']}/with_friends/">${htmlSpecialChars($userInfo['nameScreen'])}和别人</a></h2>
<ul>
<!--{if $showProtected}-->
<!--{foreach $statuses as $status}-->
<li>
	<!--{if $userInfo['idConference']}--><a href="${buildUrl('/'.$users[$status['idUser']]['nameUrl'].'/')}" rel="contact">${getDisplayName($users[$status['idUser']])}</a>：<!--{/if}-->{$status['status']}
	<span class="stamp">
	${JWStatus::GetTimeDesc($status['timeCreate'])}
	通过
	${JWDevice::GetNameFromType($status['device'], @$status['idPartner'])}${$status['statusType'] == 'SIG' ? '签名' : ''}
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/status/destroy/".$status['id']."\">悄悄话</a>" : ''}
	${($loginedUserInfo['id'] && false==JWFavourite::IsFavourite($loginedUserInfo['id'],$status['id'])) ? "<a href=\"/wo/status/favourite/".$status['id']."\">收藏</a>" : "<a href=\"/wo/status/unfavourite/".$status['idUser']."\">取消收藏</a>"}
    <a href="/wo/status/r/{$status['id']}">回复</a>
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/status/rt/".$status['id']."\">RT</a>" : ''}
	</span>
</li>
<!--{/foreach}-->
<!--{else}-->
<li>
{$userInfo['nameScreen']}只和我关注的人分享叽歪。
</li>
<!--{/if}-->
</ul>
<!--{if $showProtected}-->
{$pageString}
<!--{/if}-->

<!--{include user/profile}-->
<!--{include shortcut}-->
<!--{include footer}-->
