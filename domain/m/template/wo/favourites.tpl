<!--{include header}-->
<!--{include wo/update}-->

<h2>我收藏的更新</h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
    <a href="${buildUrl('/'.$users[$status['idUser']]['nameScreen'].'/')}">${getDisplayName($users[$status['idUser']])}</a>：{$status['status']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($status['timeCreate'])}
    通过
    ${JWDevice::GetNameFromType($status['device'], @$status['idPartner'])}
    <a href="/wo/status/unfavourite/{$status['id']}/">取消收藏</a>
    <a href="/wo/status/r/{$status['id']}">回复</a>
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
