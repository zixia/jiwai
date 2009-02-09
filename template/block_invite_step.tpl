<!--${
	$count_all = count($buddies[0]) + count($buddies[1]);
	$count_not = count($buddies[1]);
	$not_user_ids = array_keys($buddies[1]);
	$not_users = JWDB_Cache_User::GetDbRowsByIds($not_user_ids);
}-->
<div class="block">
	<div class="mar_b20">你共有&nbsp;{$count_all}&nbsp;个联系人在叽歪，未关注&nbsp;{$count_not}&nbsp;个，你可以关注他们。</div>
	<form action="/wo/invite/steep/{$cache_key}" id='f1' method="post">
	<div class="mar_b8">
		<input type="button" onclick="$('f1').submit();" value="&nbsp;关注选中的联系人&nbsp;" />
	</div>
	<div class="gray mar_b8">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t">
			<h4><input type="checkbox" name="not_follow_all" onclick="CheckAll('use_list',this.checked)" /> 选择所有联系人</h4>
		</div>
		<div id="use_list" class="f">
			<div>
				<div class="clear"></div>
			<!--{foreach $buddies[1] AS $userid=>$buddy}-->
			<!--${list($e,$n) = explode(',',$buddy);}-->
				<div class="one line">
					<div class="lt"><input type="checkbox" name="not_follow[]" value="{$userid}"/></div>
					<div class="lt hd"><a href="/{$not_users[$userid]['nameUrl']}/"><em><img src="${JWPicture::GetUrlById($not_users[$userid]['idPicture'])}" class="a1" /></em></a></div>
					<div class="mar_lt bgall">
						<h4 class="mar_b8"><a href="/{$not_users[$userid]['nameUrl']}/">{$not_users[$userid]['nameScreen']}</a></h4>
						<div align="right">
							<div class="lt">&lt;{$e}&gt;</div>
							<div>${JWLocation::GetLocationName($not_users[$userid]['location'])}</div>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			<!--{/foreach}-->
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
	<div>
		<input type="button" onclick="$('f1').submit();" value="&nbsp;关注选中的联系人&nbsp;" /> &nbsp; <input type="button" onclick="location.href='/wo/invite/steep/{$cache_key}'" value="&nbsp;跳过&nbsp;" />
	</div>
	</form>
</div>
<div class="clear"></div>
