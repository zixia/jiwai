<!--{if $g_current_user_id}-->
<!--${$b = JWTagFollower::GetTagUser($tag['id'], $g_current_user_id);}-->
<div class="side3">
<ul>
	<!--{if !$b}-->
	<li class="button side_btn">
		<div class="at"></div><div class="bt"></div>
		<div class="tt" onclick="location.href='/wo/action/ton/{$tag['id']}'">关注这个话题</div>
		<div class="bt"></div><div class="at"></div>
	</li>
	<!--{elseif 'Y'==$b['notification']}-->
	<li class="button side_btn">
		<div class="at"></div><div class="bt"></div>
		<div class="tt" onclick="location.href='/wo/action/tleave/{$tag['id']}'">取消关注</div>
		<div class="bt"></div><div class="at"></div>
	</li>
	<div class="mar_b8"></div>
	<li class="button side_btn">
		<div class="at"></div><div class="bt"></div>
		<div class="tt" onclick="location.href='/wo/action/toff/{$tag['id']}'">取消更新通知</div>
		<div class="bt"></div><div class="at"></div>
	</li>
	<!--{else}-->
	<li class="button side_btn">
		<div class="at"></div><div class="bt"></div>
		<div class="tt" onclick="location.href='/wo/action/tleave/{$tag['id']}'">取消关注</div>
		<div class="bt"></div><div class="at"></div>
	</li>
	<div class="mar_b8"></div>
	<li class="button side_btn">
		<div class="at"></div><div class="bt"></div>
		<div class="tt" onclick="location.href='/wo/action/ton/{$tag['id']}'">接收更新通知</div>
		<div class="bt"></div><div class="at"></div>
	</li>
	<!--{/if}-->
</ul>
</div>
<!--{/if}-->
