<!--{if $g_current_user_id}-->
<!--${
	$user = $g_current_user;
	$devices = JWDevice::GetDeviceRowByUserId( $g_current_user_id );
	if(isset($devices['facebook'])) unset($devices['facebook']);
	$devices = array_merge(array('web'=>'网页'),$devices);
	$count = JWSns::GetUserState($user['id']);

	$nmnum = JWDB_Cache_Message::GetNewMessageNum($user['id']);
	$nnnum = JWDB_Cache_Message::GetNewNoticeMessageNum($user['id']);
}-->
<div class="usermsg mar_b50">
	<div class="bline">
		<div class="one">
			<div class="hd lt">
				<a href="/wo/account/profile"><img src="${JWPicture::GetUrlById($user['idPicture'], 'thumb48')}" title="{$user['nameScreen']}" /></a>
			</div>
		</div>
		<ul class="msg">
			<li><h3>{$user['nameScreen']}</h3></li>
			<li class="${$nmnum ? 'lightbg' : 'bgall'}"><a href="/wo/direct_messages/">悄悄话${$nmnum ? "($nmnum)" :''}</a></li>
			<li class="${$nnnum ? 'lightbg' : 'bgall'}"><a href="/wo/direct_messages/notice">提醒${$nnnum ? "($nnnum)" :''}</a> </li>
		</ul>
		<div class="clear"></div>
	</div>
	<div class="featured mar_b8" >
		<div id="sendOjb" class="button rt">
			<div class="at"></div><div class="bt"></div>
			<div id="device" class="tt po_d">
				${JWDevice::GetNameFromType($user['deviceSendVia'])}
			</div>
			<ul id="othObj">
			<!--{foreach $devices AS $type=>$device}-->
			<!--${$type_name=JWDevice::GetNameFromType($type);}-->
				<li><a href="javascript:JiWai.ChangeDevice('{$type}','{$type_name}');">{$type_name}</a></li>
			<!--{/foreach}-->
				<div class="bt"></div><div class="at"></div>
			</ul>
			<div class="bt"></div><div class="at"></div>
		</div>
		<h4>接收通知方式：</h4>
	</div>
	<ul id="update_count" class="bgall f_14">
		<li id="friend_count"><a href="/wo/followings/" onClick="return JWAction.redirect(this);">关注&nbsp;{$count['following']}&nbsp;人</a></li>
		<li id="follower_count"><a href="/wo/followers/" onClick="return JWAction.redirect(this);">被&nbsp;{$count['follower']}&nbsp;人关注</a></li>
		<li id="follower_count"><a href="/seek/t/" onClick="return JWAction.redirect(this);">{$count['tag']}&nbsp;话题</a></li>
		<li id="friend_count"><a href="/seek/t/" onClick="return JWAction.redirect(this);">关注&nbsp;{$count['tag_following']}&nbsp;话题</a></li>
		<li id="mms_count" class="lightbg"><a href="/wo/mms/">{$count['mms']}&nbsp;张照片</a></li>
	</ul>
</div>
<!--{/if}-->
