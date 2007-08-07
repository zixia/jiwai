<!--{include header}-->

<h2>叽歪广场</h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
    <a href="${buildUrl('/'.htmlSpecialChars($users[$status['idUser']]['nameScreen']).'/')}">
        ${htmlSpecialChars($users[$status['idUser']]['nameScreen'])}
    </a>
    ${htmlSpecialChars($status['status'])}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($status['timeCreate'])}
    通过
    ${JWDevice::GetNameFromType($status['device'])}${$status['isSignature'] == 'Y' ? '签名' : ''}
    </span>
</li>
<!--{/foreach}-->
</ul>

<!--{include shortcut}-->
<!--{include footer}-->
