<!--${
	$count = count(JWDB_Cache_Status::GetTagIdsTopicByIdUser($g_page_user_id));
}-->
<div class="side3">
	<div class="pagetitle">
		&gt;&gt;&nbsp;<a href="/{$g_page_user['nameUrl']}/followers/">关注{$g_page_user['nameScreen']}的人</a>
	</div>
	<div class="pagetitle">
		&gt;&gt;&nbsp;<a href="/{$g_page_user['nameUrl']}/t/">{$g_page_user['nameScreen']}的{$count}个话题</a>
	</div>
</div>
