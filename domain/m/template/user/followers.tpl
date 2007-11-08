<!--{include header}-->

<h2><a href="/{$userInfo['nameScreen']}/friends/">${htmlSpecialChars($userInfo['nameFull'])}关注${JWFriend::GetFriendNum($userInfo['id'])}人</a>｜${htmlSpecialChars($userInfo['nameFull'])}被${JWFollower::GetFollowerNum($userInfo['id'])}人关注</h2>
<ul>
<!--{foreach $followers as $follower}-->
<li>
    <img width="48" height="48" src="${JWPicture::GetUserIconUrl($follower['id'],'thumb48')}" alt="{$follower['nameScreen']}" alt="{$follower['nameScreen']}" />
    <a href="/{$follower['nameScreen']}/">${htmlSpecialChars($follower['nameFull'])}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
