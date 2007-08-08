<!--{include header}-->
<!--{include user/shortcut}-->

<h2>{$userInfo['nameScreen']}的消息｜<a href="/{$userInfo['nameScreen']}/with_friends/">{$userInfo['nameScreen']}和好友</a></h2>
<ul>
<!--{if $showProtected}-->
<!--{foreach $statuses as $status}-->
<li>
    {$status['status']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($status['timeCreate'])}
    通过
    ${JWDevice::GetNameFromType($status['device'])}${$status['isSignature'] == 'Y' ? '签名' : ''}
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
