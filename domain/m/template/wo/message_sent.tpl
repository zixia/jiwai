<!--{include header}-->
<h2><a href="/wo/message/inbox">我收到的悄悄话</a>｜我发出的悄悄话</h2>

<ul>
<!--{foreach $messages as $message}-->
<li>
    发给 <a href="${buildUrl('/'.$users[$message['idUserReceiver']]['nameScreen'].'/')}">
        ${htmlSpecialChars($users[$message['idUserReceiver']]['nameScreen'])}
    </a>: 
    {$message['message']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($message['timeCreate'])}
    <a href="/wo/message/destroy/{$message['idMessage']}">删除</a>
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
