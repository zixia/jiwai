<!--${
	$in_ids = JWFollowerRequest::GetInRequestIds($g_current_user_id);
	$users = JWDB_Cache_User::GetDbRowsByIds( $in_ids );
}-->
<!--{if count($users)}-->
<div class="side2">
	<ul class="c_note">
	<!--{foreach $users AS $one}-->
	<li><a href="/{$one['nameUrl']}/" class="f_14">{$one['nameScreen']}</a> 想关注你了，<a href="/wo/friend_requests/accept/{$one['id']}">通过</a> | <a href="/wo/friend_requests/deny/{$one['id']}">忽略</a></li>
	</ul>
</div>
<!--{/if}-->
