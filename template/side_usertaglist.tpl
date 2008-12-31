<!--${
	$tagids = JWTagFollower::GetFollowingIds($g_page_user_id);
	$tags = JWDB_Cache_Tag::GetDbRowsByIds($tagids);
}-->
<div class="side3">
	<div class="pagetitle">
		<h3 class="lt">{$g_page_user['nameScreen']}关注${count($tagids)}个话题... &nbsp;</h3> <div class="lightbg f_gra lt">( <a href="/{$g_page_user['nameUrl']}/tfollowings/">全部</a> )</div>
		<div class="clear"></div>
	</div>
	<div class="dark">
	<!--{foreach $tags AS $tagid=>$tagrow}-->
		<a href="/t/{$tagrow['name']}/">{$tagrow['name']}</a>  
	<!--{/foreach}-->
	</div>
</div>
