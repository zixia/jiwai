<!--{include header}-->

<h2>${htmlSpecialChars($userInfo['nameScreen'])}关注${JWFollower::GetFollowingNum($userInfo['id'])}人｜<a href="/{$userInfo['nameUrl']}/followers/">${htmlSpecialChars($userInfo['nameScreen'])}被${JWFollower::GetFollowerNum($userInfo['id'])}人关注</a></h2>
<ul>
<!--{foreach $followings as $following}-->
<li>
    <a href="/{$following['nameUrl']}/" rel="contact"><img width="48" height="48" src="${JWPicture::GetUserIconUrl($following['id'],'thumb48')}" title="{$following['nameScreen']}" alt="{$following['nameScreen']}" />
    ${htmlSpecialChars($following['nameScreen'])}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
