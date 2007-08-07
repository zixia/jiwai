<!--{include header}-->

<h2><a href="/{$userInfo['nameScreen']}/friends/">{$userInfo['nameScreen']}的${JWFriend::GetFriendNum($userInfo['id'])}位好友</a>｜{$userInfo['nameScreen']}的${JWFollower::GetFollowerNum($userInfo['id'])}位粉丝</h2>
<ul>
<!--{foreach $followers as $follower}-->
<li>
    <img src="${JWPicture::GetUserIconUrl($follower['id'],'thumb48')}" alt="{$follower['nameScreen']}" alt="{$follower['nameScreen']}" />
    <a href="/{$follower['nameScreen']}">{$follower['nameScreen']}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
