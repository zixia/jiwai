<!--${$b = JWTrackUser::IsTrackUser($g_current_user_id, $key);}-->
<div class="side2">
	<!--{if !$b}-->
	<div class="mar_b20">
		<div class="button side_btn">
			<div class="at"></div><div class="bt"></div>
			<div class="tt" onclick="return JWAction.redirect('/wo/action/track/{$key}');">开始追踪</div>
			<div class="bt"></div><div class="at"></div>
		</div>
	</div>
	<div>
		追踪成功后，你将可以使用所绑定的聊天软件，开始实时的接收含有这个词汇的叽歪，是不是很酷 :)
	</div>
	<!--{else}-->
	<div class="mar_b20">
		<div class="button side_btn">
			<div class="at"></div><div class="bt"></div>
			<div class="tt" onclick="return JWAction.redirect('/wo/action/untrack/{$key}');">取消追踪</div>
			<div class="bt"></div><div class="at"></div>
		</div>
	</div>
	<!--{/if}-->
</div>
