<!--{if $g_current_user_id}-->
<!--${
	$user = $g_current_user;
	$devices = JWDevice::GetDeviceRowByUserId( $g_current_user_id );
	$devices = array_merge(array('web'=>array()),$devices);
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
			<!--{if !$device['secret']&&$type!='facebook'}-->
			<!--${$type_name=JWDevice::GetNameFromType($type);}-->
				<li><a href="javascript:JiWai.ChangeDevice('{$type}','{$type_name}');">{$type_name}</a></li>
			<!--{/if}-->
			<!--{/foreach}-->
				<div class="bt"></div><div class="at"></div>
			</ul>
			<div class="bt"></div><div class="at"></div>
		</div>
		<h4>接收通知方式：</h4>
	</div>
	<ul id="update_count" class="bgall f_14">
		<li id="friend_count"><a class="dark" href="/wo/followings/" onClick="return JWAction.redirect(this);">关注&nbsp;{$count['following']}&nbsp;人</a></li>
		<li id="follower_count"><a class="dark" href="/wo/followers/" onClick="return JWAction.redirect(this);">被&nbsp;{$count['follower']}&nbsp;人关注</a></li>
		<li id="follower_count"><a class="dark" href="/{$user['nameUrl']}/t/" onClick="return JWAction.redirect(this);">{$count['tag']}&nbsp;话题</a></li>
		<li id="friend_count"><a class="dark" href="/{$user['nameUrl']}/tfollowings/" onClick="return JWAction.redirect(this);">关注&nbsp;{$count['tag_following']}&nbsp;话题</a></li>
		<li id="mms_count"><a class="dark" href="/wo/mms/">{$count['mms']}&nbsp;张照片</a></li>
	</ul>
</div>
<!--{/if}-->
<div class="side2">
	<p><a onClick="if(confirm('将此按钮添加到浏览器的收藏夹或工具栏上，即可方便地收藏网址信息到叽歪。\r\n需要了解详细的使用方法吗？'))location.href='http://help.jiwai.de/BookmarkletUsage';return false;" href="javascript:var d=document,w=window,f='http://jiwai.de/wo/share/',l=d.location,e=encodeURIComponent,p='?u='+e(l.href)+'&amp;t='+e(d.title)+'&amp;d='+e(w.getSelection?w.getSelection().toString():d.getSelection?d.getSelection():d.selection.createRange().text);a=function(){if(!w.open(f+'s'+p,'sharer','toolbar=0,status=0,resizable=0,width=540,height=310'))l.href=f+'w'+p};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else{a()}void(0)" class="sharebtn"><img src="http://asset5.jiwai.de/images/org-share-collect.gif?1230346365" title="收藏到叽歪" /></a></p>
</div>

