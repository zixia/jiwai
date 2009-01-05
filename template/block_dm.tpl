<!--${ 
	$column = $inbox ? 'idUserSender' : 'idUserReceiver';
	$iconurl = JWTemplate::GetAssetUrl('/images/img.gif');
}-->
<!--{foreach $messages AS $one}-->
<!--${
	$user = $users[$one[$column]];
	$reply = @$replies[$one['idMessageReplyTo']];
	$avatar = JWPicture::GetUserIconUrl($user['id']);
	$timedesc = JWStatus::GetTimeDesc($one['timeCreate']);
	$through = JWDevice::GetNameFromType($one['device'],null); 
}-->
<div class="one">
	<div class="lt hd"><a href="/{$user['nameUrl']}/"><em><img src="{$avatar}" class="a1 buddy" icon="{$user['id']}"/></em></a></div>
	<div class="con">
	<!--{if $inbox && $one['messageStatusReceiver']=='notRead'}-->
	<div class="new_msg">
	<!--{/if}-->
		<div class="text dark">${JWUtility::AddLink(htmlSpecialChars($one['message']))}</div>
		<div class="f_gra">
			<div class="rt lightbg" ><!--{if $inbox && $one['messageType']=='dm'}--><a href="/wo/direct_messages/reply/{$one['id']}"><span class="ico_rebak"><img src="{$iconurl}" width="16" height="12" /></span>回复</a>&nbsp; &nbsp;<!--{/if}--><a href="/wo/direct_messages/destroy/{$one['id']}"><span class="ico_trash"><img src="{$iconurl}" width="16" height="12" /></span>删除</a></div>
			<div class="dark"><a href="/{$user['nameUrl']}">{$user['nameScreen']}</a>&nbsp;{$timedesc}&nbsp;通过&nbsp;{$through}</div>
		</div>
		<!--{if $reply}-->
		<div class="clear"></div>
		<div class="reb_msg f_gra">回复原文：{$reply['message']}</div>
		<!--{/if}-->
	<!--{if $inbox && $one['messageStatusReceiver']=='notRead'}-->
		<div class="clear"></div>
	</div>
	<!--{/if}-->
	</div>
	<div class="clear"></div>
</div>
<!--{/foreach}-->
