<!--{include header}-->

<h2><a href="/wo/friends/">我的${JWFriend::GetFriendNum($loginedUserInfo['id'])}位好友</a>｜我的${JWFollower::GetFollowerNum($loginedUserInfo['id'])}位粉丝</h2>
<ul>
<!--{foreach $followers as $follower}-->
<li>
    <img src="${JWPicture::GetUserIconUrl($follower['id'],'thumb48')}" alt="{$follower['nameScreen']}" alt="{$follower['nameScreen']}" />
    <a href="/{$follower['nameScreen']}">{$follower['nameScreen']}</a>
    <!--{if ($followerOps[ $follower['id'] ]) }-->
        <span class="a">（{$followerOps[ $follower['id'] ]}）</span>
    <!--{/if}-->
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
