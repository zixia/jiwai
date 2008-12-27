<!--${
	$user = JWUser::GetUserInfo($g_page_user_id);
	$count = JWSns::GetUserState($g_page_user_id);
	$countv = abs(intval(JWVisitUser::GetCount($g_page_user_id)));
	$avatar = JWPicture::GetUrlById($user['idPicture'], 'thumb96');
	$isother = ($g_page_on && $g_page_user_id!=$g_current_user_id);
	$wo = $isother ? $g_page_user['nameUrl'] : 'wo';
	$suggest_tags = JWFarrago::GetSuggestTag($g_current_user_id);
}-->

<div class="f">
	<form name="" action="/wo/status/update" id="updaterForm" method="post" onsubmit="$('jw_status').style.backgroundColor='#eee';">
		<div class="pagetitle">
			<span class="rt">{$count['status']}条叽歪&nbsp;<span class="f_gra">&nbsp;|&nbsp;</span>&nbsp;{$countv}次访问</span>
			<h1 cl>这一刻在做什么？</h1>
			<div class="label">
				<span class="f_gra">话题：</span>
			<!--{foreach $suggest_tags AS $one}-->
				<a href="javascript:void(0)" title="{$one}" onClick="setLabel('jw_status',this)">[{$one}]</a> 
			<!--{/foreach}-->
				<a href="javascript:void(0)" onClick="setLabel('jw_status')" title="[]">[自定义]</a>
			</div>

		</div>
		<!--{include block_updater}-->
	</form>
	
	<div class="gra_input mar_b8 rt">
	<form action="/wo/search/statuses" method="get"><input type="text" name="q" value="关键字..." onFocus="clearValue(this)" onBlur="searchValue(this,'关键字...')" /> <input type="button" onclick="this.form.submit();" value="搜索" class="def_btn" /></form>
	</div>
	<div class="clear"></div>
</div>
