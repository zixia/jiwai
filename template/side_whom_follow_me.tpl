<!--${
	if (!$size) $size = 16;
	$user_ids = JWFollower::GetFollowingIds($g_page_user_id);
	$user_ids = JWRemote::GetActivateUserId($user_ids, $size);
	$users = JWDB_Cache_User::GetDbRowsByIds($user_ids);
	$avatars = JWFunction::GetColArrayFromRows($users,'idPicture');
	$avatars = JWPicture::GetUrlRowByIds($avatars);
	$u = $g_page_on ? $g_page_user['nameUrl'] : 'wo';
}-->
<!--{if count($users)}-->
<div class="side3">
	<div class="pagetitle">
		<h3 class="lt">关注我的人... &nbsp;</h3> <div class="lightbg f_gra lt">（<a href="/wo/followings/">全部</a>）</div>
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
