<!--{if $g_current_user_id||$g_page_user_id}-->
<!--${
	if (!$size) $size = 40;
	$user_ids = JWFollower::GetFollowingIds($g_page_user_id);
	$users = JWUser::GetDbRowsByIdsAndOrderByActivate($user_ids, $size);
	$avatars = JWFunction::GetColArrayFromRows($users,'idPicture');
	$avatars = JWPicture::GetUrlRowByIds($avatars);
	$u = $g_page_on ? $g_page_user['nameUrl'] : 'wo';
	$whom = $g_page_on ? $g_page_user['nameScreen'] : '';
}-->
<div class="side2">
	<div class="pagetitle">
		<h3 class="lt">{$whom}关注的人... &nbsp;</h3> <div class="lightbg f_gra lt">( <a href="/{$u}/followings/">全部</a> )</div>
		<div class="clear"></div>
	</div>
	<ul class="one">
	<!--{foreach $users AS $one}-->
		<li class="hd"><a href="/{$one['nameUrl']}/" title="{$one['nameScreen']}"><img src="{$avatars[$one['idPicture']]}" title="{$one['nameScreen']}" class="buddy" icon="{$one['id']}" /> {$one['nameScreen']}</a></li>
	<!--{/foreach}-->
		<div class="clear"></div>
	</ul>
	<div class="clear"></div>
</div>
<!--{/if}-->
