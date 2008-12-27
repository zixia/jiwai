<!--{if $g_current_user_id && $g_page_user_id!=$g_current_user_id}-->
<!--${
	$is_blocked = JWBlock::IsBlocked($g_current_user_id, $g_page_user_id);
}-->
<div class="side5">
	<ul class="c_note">
	<li><a href="/wo/design/save/{$g_page_user_id}">使用此人配色</a></li>
	<li>
		<!--{if $is_blocked}-->
		<a href="/wo/block/u/{$g_page_user_id}">解除阻止</a>
		<!--{else}-->
		<a href="/wo/block/b/{$g_page_user_id}">阻止{$g_page_user['nameScreen']}</a>
		<!--{/if}-->
	</li>
	</ul>
</div>
<!--{/if}-->
