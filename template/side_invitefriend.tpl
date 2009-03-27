<!--{if $g_current_user_id==89}-->
<div class="side3">
	<div>复制下面的网址发送给朋友，当你的朋友接受邀请并注册后，你们将自动的相互关注。</div>
	<input value="http://JiWai.de/wo/invitations/i/${JWUser::GetIdEncodedFromIdUser($g_current_user_id)}" onclick="this.select();" size="30" />
	<div>你还可以通过更多的方式<a href="/wo/invite/">邀请朋友</a>。</div>
</div>
<div class="clear"></div>
<!--{/if}-->
