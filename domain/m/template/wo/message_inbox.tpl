<!--{include header}-->
<h2>我收到的悄悄话｜<a href="/wo/message/sent">我发出的悄悄话</a></h2>
<ul>
<!--{foreach $messages as $message}-->
<li>
    来自 <a href="${buildUrl('/'.$users[$message['idUserSender']]['nameUrl'].'/')}" rel="contact">${getDisplayName($users[$message['idUserSender']])}</a>：{$message['message']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($message['timeCreate'])}
    <a href="/wo/message/create/{$message['idUserSender']}">回复</a>｜<a href="/wo/message/destroy/{$message['idMessage']}">删除</a>
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
