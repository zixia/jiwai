<!--{include header}-->

<h2><a href="/wo/followings/">我关注的人(${JWFollower::GetFollowingNum($loginedUserInfo['id'])})</a>｜关注我的人(${JWFollower::GetFollowerNum($loginedUserInfo['id'])})</h2>
<ul>
<!--{foreach $followers as $follower}-->
<li>
    <a href="/{$follower['nameUrl']}/" rel="contact"><img width="48" height="48" src="${JWPicture::GetUserIconUrl($follower['id'],'thumb48')}" alt="{$follower['nameScreen']}" alt="{$follower['nameScreen']}" />
    ${htmlSpecialChars($follower['nameScreen'])}</a>
    <!--{if ($followerOps[ $follower['id'] ]) }-->
        <span class="a">（{$followerOps[ $follower['id'] ]}）</span>
    <!--{/if}-->
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
