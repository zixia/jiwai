<!--${
	$count = JWSns::GetUserState($g_page_user_id);
	$countv = JWVisitUser::GetCount($g_page_user_id);
}-->
<div class="side3">
	<ul class="show_num">
		<li class="ll"><strong>关注数</strong></li>
		<li><strong>被关注</strong></li>
		<li><strong>叽歪数</strong>
		</li><li><strong>被访问</strong></li>
		<li class="ll"><em>{$count['following']}</em></li>
		<li><em>{$count['follower']}</em></li>
		<li><em>{$count['status']}</em></li>
		<li><em>{$countv}</em></li>
	</ul>
</div>	
