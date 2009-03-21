<!--${
	$user = JWUser::GetUserInfo($g_page_user_id);
	$iconurl = JWTemplate::GetAssetUrl('/images/img.gif');
	if ( !$thread_id )
		$thread_id = JWStatus::GetHeadStatusId($g_page_user_id);
	if ( $thread_id )
		$one = JWDB_Cache_Status::GetDbRowById( $thread_id );

	if ( $one)
	{
		$user = JWUser::GetUserInfo($one['idUser']);
		$is_protected = JWSns::IsProtectedStatus($one, $g_current_user_id);
		$can_delete = JWStatus::IsUserCanDelStatus($g_current_user_id, $thread_id);
		$is_favourited = JWFavourite::IsFavourite($g_current_user_id, $thread_id); 

		if ( $one['statusType'] == 'MMS' ) 
		{
			$photo_url = JWPicture::GetUrlById($user['idPicture']);
		}
		else if ( !empty($one['idPicture']) ) 
		{
			$photo_url = JWPicture::GetUrlById($one['idPicture']);
		}
		else 
		{
			$photo_url = JWTemplate::GetAssetUrl('/images/org-nobody-48-48.gif');
		}

		if( !$is_protected)
		{
			$plugin_result = JWPlugins::GetPluginResult( $one );
			$formated_one = JWStatus::FormatStatus($one);
		}
		else
		{
			$plugin_result = "";
			$formated_one = array(
				'status' => '<div class="pad bg_yel"><h2>我只和我关注的人分享我的叽歪。</h2></div>',
				'replyto' => NULL,
				'protected' => true,
				);
		}
		$replyto = $formated_one['replyto'] 
			? $formated_one['replyto'] : $user['nameUrl'];
		$replyurl = $replyto;
		if ($one['idThread']) {
			$rone = JWDB_Cache_Status::GetDbRowById($one['idThread']);
			$ruser = $rone ? JWUser::GetUserInfo($rone['idUser']) : $user;
			$replyurl = $ruser['nameUrl'];
		}
		$replynum = $one['idThread'] ? 0 : JWDB_Cache_Status::GetCountReply($thread_id);
		$replyid = ($one['idThread'] ? $one['idThread'].'/':'') . $thread_id;
		$through = JWDevice::GetNameFromType($one['device'],$one['idPartner']) . @$_INI['type']['S_'.$one['statusType']];
	}
	else
	{
		$formated_one = array(
				'status'=>'目前为止没有叽歪过。'
				);
	}
	$avatar = JWPicture::GetUrlById($user['idPicture'], 'thumb96');
}-->
<div class="top_block">
	<div class="usermsg">
		<div class="lt wht hdimg">
			<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
			<div class="f">
				<div class="hd mar_b8">
					<a href="/{$user['nameUrl']}/avatar/"><img src="{$avatar}" title="{$user['nameScreen']}" /></a>
				</div>
				<!--{if true}-->
				<!--${$action = JWSns::GetUserAction($g_current_user_id, $g_page_user_id);}-->
				<div class="mar_b8" >
					<!--{if !$action||$action['follow']}-->
					<div class="button sbtn">
						<div class="at"></div><div class="bt"></div>
						<div class="tt">
							<a href="/wo/action/follow/{$g_page_user_id}" onclick="return JWAction.follow({$g_page_user_id});">关注此人</a>
						</div>
						<div class="bt"></div><div class="at"></div>
					</div>
					<!--{else}-->
					<div class="bg_dark">
						已关注 &gt;&gt; <a href="/wo/action/leave/{$g_page_user_id}" onclick="return JWAction.redirect(this);" class="bg_dark">取消</a>
					</div>
					<!--{/if}-->
				</div>

				<div id="update_count">
					<a href="/wo/direct_messages/create/{$user['id']}" class="ico_mail"  onclick="return JWAction.redirect(this);" title="发送悄悄话"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="12" /></a>
					<a href="/wo/action/nudge/{$user['id']}" class="ico_nao" onclick="return JWAction.redirect(this);" title="挠挠此人"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="12" /></a>
					<!--{if $action['on']}-->
					<a href="/wo/action/on/{$user['id']}" class="ico_nosound" title="接受更新通知"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="16" /></a>
					<!--{elseif $action&&!$action['follow']&&!$action['on']}-->
					<a href="/wo/action/off/{$user['id']}" class="ico_nosound" title="取消更新通知"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="16" /></a>
					<!--{/if}-->
					<!--{if $action['block']===false}-->
					<a href="/wo/block/u/{$user['id']}" class="ico_stop" title="取消阻止"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="16" /></a>
					<!--{else}-->
					<a href="/wo/block/b/{$user['id']}" class="ico_stop" title="阻止此人" onclick="return JWAction.redirect(this);"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="16" /></a>
					<!--{/if}-->
				</div>
				<!--{/if}-->
			</div>
			<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
		</div>
		<div class="msg phot_mar">
			<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
			<div class="f">
				<div class="rt txt_r"><a href="/wo/design/save/{$user['id']}" class="dark">使用此人配色</a></div>
				<h1>{$user['nameScreen']}</h1>
				<div class="po_lt"></div>
				<div class="f_14 mar_b8">{$formated_one['status']}<!--{if isset($plugin_result['html'])}--><div class="bg_black">{$plugin_result['html']}</div><!--{/if}--></div>
				<!--{if $formated_one['protected']}-->
				<div class="pad hand" onMouseover="this.className+=' bg_gra'" onMouseOut="this.className=this.className.replace(' bg_gra','')" onClick="var o=$('protect_info');o.className=o.className.replace(' no','')">
					<div class="txt_r mar_b8">如何获得{$user['nameScreen']}的关注？</div>
					<ul id="protect_info" class="dot_b f_gra no">
						<li>你可以先关注此人，说不定{$user['nameScreen']}就会关注你哦。</li>
						<li>你还可以发悄悄话与{$user['nameScreen']}进行交流，要小心，可别造成骚扰哦，  被{$user['nameScreen']}阻止了可不是件好事。</li>
					</ul>
				</div>
				<!--{/if}-->
				<div class="f_gra">
				<!--{if $one}-->
					<div class="rt lightbg"><a href="/{$replyurl}/thread/{$replyid}" class="thread_item" rel="{$one['id']}:{$user['nameScreen']}"><span class="ico_rebak"><img src="{$iconurl}" width="16" height="12" /></span>${$replynum ? $replynum.'条':''}回复</a><!--{if $g_current_user_id}-->&nbsp; &nbsp;<a href="/wo/favourites/${$is_favourited?"create":"create"}/{$one['id']}" onclick="return JWAction.toggleStar({$one['id']});" id="status_star_{$one['id']}" title="${$is_favourited?"取消收藏":"收藏它"}"><span id="ico_star_{$one['id']}" class="ico_fav${$is_favourited?'d':''}"><img src="{$iconurl}" width="16" height="12" /></span>${$is_favourited?"取消收藏":"收藏"}</a><!--{/if}--><!--{if $can_delete}-->&nbsp; &nbsp;<a href="/wo/status/destroy/{$one['id']}" class="c_note" onclick="return JWAction.doTrash({$one['id']})"><span class="ico_trash"><img src="{$iconurl}" width="16" height="12" /></span>删除</a><!--{/if}--></div>
					<div class="dark"><a href="/{$user['nameUrl']}/" title="{$user['nameFull']}">{$user['nameScreen']}</a>&nbsp;<a href="/{$user['nameUrl']}/statuses/{$thread_id}" class="f_gra" title="{$one['timeCreate']}">${JWStatus::GetTimeDesc($one['timeCreate'])}</a>&nbsp;通过&nbsp;{$through}</div>
				<!--{else}--><br/><!--{/if}-->
				</div>
			</div>
			<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
		</div>
		<div class="clear"></div>
	</div>
</div>
