<!--{include header}-->
<!--{include user/shortcut}-->

<h2><a href="/{$userInfo['nameScreen']}/">{$userInfo['nameScreen']}的消息</a>｜{$userInfo['nameScreen']}和好友</h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
    <a href="${buildUrl('/'.$users[$status['idUser']]['nameScreen'].'/')}">
        ${htmlSpecialChars($users[$status['idUser']]['nameScreen'])}
    </a>: 
    {$status['status']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($status['timeCreate'])}
    通过
    ${JWDevice::GetNameFromType($status['device'])}${$status['isSignature'] == 'Y' ? '签名' : ''}
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include user/profile}-->
<!--{include shortcut}-->
<!--{include footer}-->
