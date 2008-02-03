<!--{include header}-->

<h2>我关注的${JWFollower::GetFollowingNum($loginedUserInfo['id'])}人｜<a href="/wo/followers/">关注我的${JWFollower::GetFollowerNum($loginedUserInfo['id'])}人</a></h2>
<ul>
<!--{foreach $followings as $following}-->
<li>
    <a href="/{$following['nameUrl']}/" rel="contact"><img width="48" height="48" src="${JWPicture::GetUserIconUrl($following['id'],'thumb48')}" alt="{$following['nameScreen']}" alt="{$following['nameScreen']}" />
    ${htmlSpecialChars($following['nameScreen'])}</a>
    <span class="a">（{$actionOps[ $following['id'] ]}）</span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
