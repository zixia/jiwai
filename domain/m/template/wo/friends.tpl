<!--{include header}-->

<h2>我的${JWFriend::GetFriendNum($loginedUserInfo['id'])}位好友｜<a href="/wo/followers/">我的${JWFollower::GetFollowerNum($loginedUserInfo['id'])}位粉丝</a></h2>
<ul>
<!--{foreach $friends as $friend}-->
<li>
    <img src="${JWPicture::GetUserIconUrl($friend['id'],'thumb48')}" alt="{$friend['nameScreen']}" alt="{$friend['nameScreen']}" />
    <a href="/{$friend['nameScreen']}">{$friend['nameScreen']}</a>
    <span class="a">（{$friendOps[ $friend['id'] ]}）</span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
