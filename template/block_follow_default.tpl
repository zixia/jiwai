<!--{foreach $friend_ids AS $user_id}-->
<!--${
	$user_row = $friend_user_rows[$user_id];
	$user_action_row = $user_action_rows[$user_id];
	$user_picture_id = @$user_row['idPicture'];
	$user_icon_url = JWTemplate::GetConst('UrlStrangerPicture');
	if ( $user_picture_id )
		$user_icon_url = $picture_url_row[$user_picture_id];
}-->
<div class="one line">
	<div class="lt hd"><a href="/{$user_row['nameUrl']}/"><em><img src="{$user_icon_url}" class="a1" /></em></a></div>
	<div class="rt">
		<div class="dark">
			<div><a href="{$user_row['nameUrl']}">{$user_row['nameScreen']}</a></div>
			<div>
				更新通知：
				<input name="radio_{$user_id}" type="radio" value="1" <!--${if(!$user_action_row['on']) echo "checked";}--> onclick="JWAction.ajaxFollow({$user_id}, 1);"/>
				打开
				<input name="radio_{$user_id}" type="radio" value="2" <!--${if($user_action_row['on']) echo "checked";}--> onclick="JWAction.ajaxFollow({$user_id}, 2);"/>
				关闭
			</div>
		</div>
		<div class="f_gra">
			<div class="rt lightbg" ><a href="/wo/direct_messages/create/{$user_id}">悄悄话</a> &nbsp;|&nbsp; <a href="/wo/action/nudge/{$user_id}" onclick="return JWAction.redirect(this);">挠挠</a> &nbsp;|&nbsp; <a href="/wo/action/off/{$user_id}" onclick="JWAction.redirect(this);">取消关注</a> &nbsp;|&nbsp; <a href="/wo/block/b/{$user_id}">阻止此人</a></div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<!--{/foreach}-->
