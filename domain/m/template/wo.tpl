<card title="叽歪de/{$userInfo['nameScreen']}">
{$userInfo['nameScreen']}的消息|<a href="/{$userInfo['nameScreen']}/with_friends/">{$userInfo['nameScreen']}和好友</a><br/>
<!--{foreach $statuses as $status}-->
    {$status['status']}<br/>
<!--{/foreach}-->
</card>
