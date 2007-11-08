<!--{include header}-->

<h2>我关注的${JWFriend::GetFriendNum($loginedUserInfo['id'])}人｜<a href="/wo/followers/">关注我的${JWFollower::GetFollowerNum($loginedUserInfo['id'])}人</a></h2>
<ul>
<!--{foreach $friends as $friend}-->
<li>
    <img width="48" height="48" src="${JWPicture::GetUserIconUrl($friend['id'],'thumb48')}" alt="{$friend['nameScreen']}" alt="{$friend['nameScreen']}" />
    <a href="/{$friend['nameScreen']}/">${htmlSpecialChars($friend['nameFull'])}</a>
    <span class="a">（{$friendOps[ $friend['id'] ]}）</span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
