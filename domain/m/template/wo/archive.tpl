<!--{include header}-->
<!--{include wo/update}-->

<h2>最新叽歪｜<a href="/wo/replies">叽友回复</a></h2>
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
    ${JWDevice::GetNameFromType($status['device'], $status['idPartner'])}${$status['isSignature'] == 'Y' ? '签名' : ''}
    ${($loginedUserInfo['id']==$status['idUser']) ? "<a href=\"/wo/status/destroy/".$status['id']."\">删除</a>" : ''}
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
