<!--{include header}-->
<!--{include wo/update}-->

<h2>我和朋友在做什么……</h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
    <a href="${buildUrl('/'.$users[$status['idUser']]['nameScreen'].'/')}">
        ${htmlSpecialChars($users[$status['idUser']]['nameFull'])}
    </a>: 
    {$status['status']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($status['timeCreate'])}
    通过
    ${JWDevice::GetNameFromType($status['device'])}${$status['isSignature'] == 'Y' ? '签名' : ''}
    ${($loginedUserInfo['id']==$status['idUser']) ? "<a href=\"/wo/status/destroy/".$status['id']."\">删除</a>" : ''}
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
