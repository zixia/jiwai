<!--{include header}-->
<!--{include user/shortcut}-->

<h2><a href="/{$userInfo['nameScreen']}/">${htmlSpecialChars($userInfo['nameFull'])}的消息</a>｜${htmlSpecialChars($userInfo['nameFull'])}和好友</h2>
<ul>
<!--{if $showProtected}-->
	<!--{foreach $statuses as $status}-->
	<!--${
		$protected = ( $users[$status['idUser']]['protected'] == 'Y' 
						&& $status['idUser'] != $loginedUserInfo['id']
						&& false == JWFriend::IsFriend($status['idUser'], $loginedUserInfo['id'])
					 );
	}-->
	<!--{if (false == $protected)}-->
		<li>
			<a href="${buildUrl('/'.$users[$status['idUser']]['nameScreen'].'/')}">${getDisplayName($users[$status['idUser']])}</a>：{$status['status']}
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
