<!--${
	if ( $reply ) {
		$to = JWUser::GetUserInfo($reply['idUserSender']);
		$param = array('reply' => $reply);
		$replyto = $to;
	} else {
		$bio_ids = JWFollower::GetBioFollowingIds($g_current_user_id);
		$receivers = JWDB_Cache_User::GetDbRowsByIds($bio_ids);
		$choice = true;
	}
}-->
<form action="/wo/direct_messages/create" id="updaterForm" method="post" onsubmit="$('jw_status').style.backgroundColor='#eee';">
	<div class="pagetitle">
		<div>
		<!--{if $reply}-->
			<h1>回复悄悄话</h1>
		<!--{else if $to}-->
			<h1>发送悄悄话给{$to['nameScreen']}</h1>
		<!--{else}-->
			<h1>悄悄话</h1>
		<!--{/if}-->

		<!--{if $reply}-->
			<input type="hidden" name="dm_message_id" value="{$reply['id']}" />
			<input type="hidden" name="dm_user_id" value="{$reply['idUserSender']}" />
			${JWElement::Instance()->block_dm($param);}
		<!--{else if $to}-->
			<input type="hidden" name="dm_user_id" value="{$to['id']}" />
		<!--{else}-->
			发送悄悄话给：
			<select id="dm_user_id" name="dm_user_id" onchange="if($('jw_dm_user_id')) $('jw_dm_user_id').value=$(this).value;">
			<option value="">---请选择---</option>
			<!--{foreach $receivers AS $one}-->
			<option value="{$one['id']}">{$one['nameScreen']}</option>
			<!--{/foreach}-->
			</select>
		<!--{/if}-->
		</div>
	</div>

	<div class="update">
	<!--{if $replyto}-->
		<div class="mar_b8"><b>悄悄回复{$replyto['nameScreen']}：</b></div>
	<!--{/if}-->
		<textarea id="jw_status" name="jw_status" onKeyDown="if(this.value.length>0&&((event.ctrlKey&&event.keyCode==13)||(event.altKey&&event.keyCode==83))){JWAction.updateStatus();return false;}" onKeyUp="textCounter(this.form.jw_status,$('count'),70);"></textarea>
		<ul class="f_gra">
			<li class="lt">1条短信还剩 <span id="count">70</span> 字</li>
			<li class="rt">Ctrl+Enter直接叽歪</li>
			<li class="button">

				<div class="at"></div><div class="bt"></div>
				<div class="tt" onclick="if($('jw_status').value.length>0){return JWAction.updateStatus();}else{$('jw_status').focus();}" >叽歪一下</div>
				<div class="bt"></div><div class="at"></div>
			</li>
		</ul>
	</div>
</form>
<div class="clear"></div>
