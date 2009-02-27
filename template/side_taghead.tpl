<div class="side1">
	<div class="pagetitle">
	<!--{if $tag}-->
		<!--{if $g_page_on}-->
		<h3 class="mar_b8"><a href="/t/{$tag['name']}/">大家的&nbsp;[{$tag['name']}]&nbsp;话题</a></h3>
		<!--{elseif $g_current_user_id}-->
		<h3 class="mar_b8"><a href="/{$g_current_user['nameUrl']}/t/{$tag['name']}/">我的该话题叽歪</a></h3>
		<!--{/if}-->
	<!--{/if}-->
		<h3 class="mar_b8"><a href="/t/">大家的话题</a></h3>
		<h3 class="mar_b8"><a href="/k/">热门的词汇</a></h3>
	</div>
</div>
