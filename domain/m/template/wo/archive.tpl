<!--{include header}-->
<!--{include wo/update}-->

<!--${
	$msgCount = JWMessage::GetMessageStatusNum($loginedUserInfo['id'], JWMessage::INBOX, JWMessage::MESSAGE_NOTREAD);
	$msgString = ( $msgCount == 0 ) ? '' : '('.$msgCount.'条)';
}-->
<h2>最新｜<a href="/wo/replies/">回复</a>｜<a href="/wo/message/inbox">悄悄话{$msgString}</a></h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
	<a href="${buildUrl('/'.$users[$status['idUser']]['nameUrl'].'/')}" rel="contact">${getDisplayName($users[$status['idUser']])}</a>：{$status['status']}
	<span class="stamp">
	${JWStatus::GetTimeDesc($status['timeCreate'])}
	通过
	${JWDevice::GetNameFromType($status['device'], $status['idPartner'])}${$status['statusType'] == 'SIG' ? '签名' : ''}
	${($loginedUserInfo['id']==$status['idUser']) ? "<a href=\"/wo/status/destroy/".$status['id']."\">删除</a>" : ''}
	${($loginedUserInfo['id'] && false==JWFavourite::IsFavourite($loginedUserInfo['id'],$status['id'])) ? "<a href=\"/wo/status/favourite/".$status['id']."\">收藏</a>" : ''}
    <a href="/wo/status/r/{$status['id']}">回复</a>
	</span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
