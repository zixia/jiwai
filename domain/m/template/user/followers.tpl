<!--{include header}-->

<h2><a href="/{$userInfo['nameUrl']}/followings/">${htmlSpecialChars($userInfo['nameScreen'])}关注${JWFollower::GetFollowingNum($userInfo['id'])}人</a>｜${htmlSpecialChars($userInfo['nameScreen'])}被${JWFollower::GetFollowerNum($userInfo['id'])}人关注</h2>
<ul>
<!--{foreach $followers as $follower}-->
<li>
    <a href="/{$follower['nameUrl']}/" rel="contact"><img width="48" height="48" src="${JWPicture::GetUserIconUrl($follower['id'],'thumb48s')}" title="{$follower['nameScreen']}" alt="{$follower['nameScreen']}" />
    ${htmlSpecialChars($follower['nameScreen'])}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
