<!--{if $g_current_user_id}-->
<div class="side3">
	<div class="pagetitle">
		<h3 class="lt">发送链接邀请朋友...</h3>
		<div class="clear"></div>
	</div>
	<div>复制下面的网址发送给朋友，当你的朋友接受邀请并注册后，你们将自动的相互关注。</div>
	<input value="http://JiWai.de/wo/invitations/i/${JWUser::GetIdEncodedFromIdUser($g_current_user_id)}" onclick="this.select();" size="30" />
	<div>你还可以通过更多的方式<a href="/wo/invite/">邀请朋友</a>。</div>
</div>
<!--{/if}-->
<div class="clear"></div>
