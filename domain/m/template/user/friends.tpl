<!--{include header}-->

<h2>${htmlSpecialChars($userInfo['nameFull'])}的${JWFriend::GetFriendNum($userInfo['id'])}位好友｜<a href="/{$userInfo['nameScreen']}/followers/">${htmlSpecialChars($userInfo['nameFull'])}的${JWFollower::GetFollowerNum($userInfo['id'])}位粉丝</a></h2>
<ul>
<!--{foreach $friends as $friend}-->
<li>
    <img width="48" height="48" src="${JWPicture::GetUserIconUrl($friend['id'],'thumb48')}" alt="{$friend['nameScreen']}" alt="{$friend['nameScreen']}" />
    <a href="/{$friend['nameScreen']}/">{$friend['nameScreen']}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
