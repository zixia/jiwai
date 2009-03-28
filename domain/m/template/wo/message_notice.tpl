<!--{include header}-->
<h2><a href="/wo/message/inbox">收件箱</a>｜<a href="/wo/message/sent">发件箱</a>｜提醒</h2>
<ul>
<!--{foreach $messages as $message}-->
<li>
    来自 <a href="${buildUrl('/'.$users[$message['idUserSender']]['nameUrl'].'/')}" rel="contact">${getDisplayName($users[$message['idUserSender']])}</a>：{$message['message']}
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
