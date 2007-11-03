<!--{include header}-->
<!--{include user/shortcut}-->

<h2>${htmlSpecialChars($userInfo['nameFull'])}的消息｜<a href="/{$userInfo['nameScreen']}/with_friends/">${htmlSpecialChars($userInfo['nameFull'])}和好友</a></h2>
<ul>
<!--{if $showProtected}-->
<!--{foreach $statuses as $status}-->
<li>
	<!--{if $userInfo['idConference']}--><a href="${buildUrl('/'.$users[$status['idUser']]['nameScreen'].'/')}">${getDisplayName($users[$status['idUser']])}</a>：<!--{/if}-->{$status['status']}
	<span class="stamp">
	${JWStatus::GetTimeDesc($status['timeCreate'])}
	通过
	${JWDevice::GetNameFromType($status['device'], @$status['idPartner'])}${$status['isSignature'] == 'Y' ? '签名' : ''}
	${($loginedUserInfo['id'] && false==JWFavourite::IsFavourite($loginedUserInfo['id'],$status['id'])) ? "<a href=\"/wo/status/favourite/".$status['id']."\">收藏</a>" : ''}
	</span>
</li>
<!--{/foreach}-->
<!--{else}-->
<li>
{$userInfo['nameScreen']}只和好友分享叽歪。
</li>
<!--{/if}-->
</ul>
<!--{if $showProtected}-->
{$pageString}
<!--{/if}-->

<!--{include user/profile}-->
<!--{include shortcut}-->
<!--{include footer}-->
