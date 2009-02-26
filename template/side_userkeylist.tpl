<!--${
	$words = JWTrackUser::GetWordListByIdUser($g_page_user_id, false);
}-->
<div class="side3">
	<div class="pagetitle">
		<h3 class="lt">{$g_page_user['nameScreen']}追踪${count($words)}个词汇... &nbsp;</h3> <div class="lightbg f_gra lt">( <a href="/{$g_page_user['nameUrl']}/kfollowings/">全部</a> )</div>
		<div class="clear"></div>
	</div>
	<div class="dark">
	<!--{foreach $words AS $word}-->
		<a href="/k/{$word}/">{$word}</a>  
	<!--{/foreach}-->
	</div>
</div>
