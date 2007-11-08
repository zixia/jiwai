<!--{include header}-->

<h2>${htmlSpecialChars($userInfo['nameFull'])}关注${JWFriend::GetFriendNum($userInfo['id'])}人｜<a href="/{$userInfo['nameScreen']}/followers/">${htmlSpecialChars($userInfo['nameFull'])}被${JWFollower::GetFollowerNum($userInfo['id'])}人关注</a></h2>
<ul>
<!--{foreach $friends as $friend}-->
<li>
    <img width="48" height="48" src="${JWPicture::GetUserIconUrl($friend['id'],'thumb48')}" alt="{$friend['nameScreen']}" alt="{$friend['nameScreen']}" />
    <a href="/{$friend['nameScreen']}/">${htmlSpecialChars($friend['nameFull'])}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
