<!--${		
	$is_favourited_array = JWFavourite::IsFavourited($g_current_user_id, array_keys($status_rows));
	$iconurl = JWTemplate::GetAssetUrl('/images/img.gif');
}-->
<!--{foreach $status_rows AS $status_id=>$one}-->
<!--${
	$user_id = $one['idUser'];
	$user = $user_rows[$user_id];

	$can_delete = JWStatus::IsUserCanDelStatus($g_current_user_id, $status_id);
	$is_favourited = $is_favourited_array[$status_id];

	$is_protected = JWSns::IsProtectedStatus($one, $g_current_user_id);
	if ( $is_skip && $is_protected)
		continue;

	if ( $one['statusType'] == 'MMS' ) {
		$photo_url = JWPicture::GetUrlById($user['idPicture']);
	} else {
        $idPicture = empty($one['idPicture'])
            ? $user['idPicture']
            : $one['idPicture'];
		$photo_url=JWPicture::GetUrlById($idPicture,'thumb48');
	}

	if(!$is_protected) {
		$plugin_result = JWPlugins::GetPluginResult( $one );
		$formated_one = JWStatus::FormatStatus($one);
	} else {
		$plugin_result = "";
		$formated_one = array(
			'status'=>'我只和我关注的人分享我的叽歪。',
			'replyto' => NULL,
			);
	}
	$replyto = $formated_one['replyto'] ? $formated_one['replyto'] : null;
	$replytoname = $formated_one['replytoname'] ? $formated_one['replytoname'] : null;
	$thread = $one['idThread'] ? JWDB_Cache_Status::GetDbRowById($one['idThread']) : null;
	$thread_user = $thread ? JWUser::GetUserInfo($thread['idUser']) : null;
	$replyurl = $thread_user ? $thread_user['nameUrl'] : $user['nameUrl'];
	$replynum = $one['idThread'] ? 0 : JWDB_Cache_Status::GetCountReply($status_id);
	$replyid = ($one['idThread'] ? $one['idThread'].'/':'') . $status_id;
	$through = JWDevice::GetNameFromType($one['device'],$one['idPartner']) . @$_INI['type']['S_'.$one['statusType']];
}-->
<div class="one" id="status_{$one['id']}">
	<div class="lt hd">
		<a href="/{$user['nameUrl']}/" title="{$user['nameFull']}"><em><img src="{$photo_url}" class="a1 buddy" icon="{$user_id}" alt="{$user['nameFull']}" title="{$user['nameFull']}"/></em></a>
	</div>
	<div class="con">
		<div class="text dark">${JWUtility::HighLight($formated_one['status'])}<!--{if isset($plugin_result['html'])}--><div class="bg_black">{$plugin_result['html']}</div><!--{/if}--></div>
		<div class="f_gra">
			<div class="rt lightbg"><a rel="{$one['id']}:{$user['nameScreen']}" onclick="return JWAction.replyStatus('{$user['nameScreen']}','{$one['idUser']}','{$one['id']}');" href="/{$replyurl}/thread/{$replyid}"><span class="ico_rebak"><img src="{$iconurl}" width="16" height="12" /></span>${$replynum ? $replynum.'条':''}回复</a><!--{if $g_current_user_id}-->&nbsp; &nbsp;<a href="/wo/favourites/${$is_favourited?"create":"create"}/{$one['id']}" onclick="return JWAction.toggleStar({$one['id']});" id="status_star_{$one['id']}" title="${$is_favourited?"取消收藏":"收藏它"}"><span id="ico_star_{$one['id']}" class="ico_fav${$is_favourited?'d':''}"><img src="{$iconurl}" width="16" height="12" /></span>${$is_favourited?"取消收藏":"收藏"}</a><!--{/if}--><!--{if $can_delete}-->&nbsp; &nbsp;<a href="/wo/status/destroy/{$one['id']}" class="c_note" onclick="return JWAction.doTrash({$one['id']})"><span class="ico_trash"><img src="{$iconurl}" width="16" height="12" /></span>删除</a><!--{/if}--></div>
			<div class="dark"><a href="/{$user['nameUrl']}/" title="{$user['nameFull']}">{$user['nameScreen']}</a>&nbsp;<a href="/{$user['nameUrl']}/statuses/{$status_id}" class="f_gra" title="{$one['timeCreate']}">${JWStatus::GetTimeDesc($one['timeCreate'])}</a>&nbsp;通过&nbsp;{$through}<!--{if $one['idStatusReplyTo']&&$replyto}-->&nbsp;<a href="/{$replyto}/${$one['idStatusReplyTo']? 'statuses/'.$one['idStatusReplyTo']:''}" class="f_gra">给{$replytoname}的回复</a><!--{/if}--></div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<!--{/foreach}-->
