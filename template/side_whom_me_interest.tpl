<!--{if $g_current_user_id && 8>JWDB_Cache_Follower::GetFollowingNum($g_current_user_id)}-->
<!--${
	$interestuserids = JWUser::GetFeaturedUserIds(8, 'commender');
	$followinguserids = JWFollower::GetFollowingIds($g_current_user_id);
	$diffuserids = array_diff($interestuserids, $followinguserids);
	$users = JWDB_Cache_User::GetDbRowsByIds($diffuserids);
	$avatars = JWFunction::GetColArrayFromRows($users,'idPicture');
	$avatars = JWPicture::GetUrlRowByIds($avatars);
}-->
<div class="side2">
	<div class="pagetitle">
		<h3 class="lt">你可能会感兴趣的人... &nbsp;</h3>
		<div class="clear"></div>
	</div>
	<ul class="one">
	<!--{foreach $users AS $one}-->
		<li class="hd"><a href="/{$one['nameUrl']}/" title="{$one['nameScreen']}"><img src="{$avatars[$one['idPicture']]}" title="{$one['nameScreen']}" class="buddy" icon="{$one['id']}" /> {$one['nameScreen']}</a></li>
	<!--{/foreach}-->
		<div class="clear"></div>
	</ul>
	<div class="clear"></div>
</div>
<!--{/if}-->
