<!--{if $g_current_user_id}-->
<!--${
	if (!$size) $size = 8;
	$vistor_user_ids = JWSns::GetIdUserVistors($g_page_user_id, $g_current_user_id, $size);
	$vistors = JWDB_Cache_User::GetDbRowsByIds($vistor_user_ids);
	$pic_ids = JWFunction::GetColArrayFromRows($vistors,'idPicture');
	$avatars = JWPicture::GetUrlRowByIds($pic_ids);
}-->
<!--{if count($vistors)}-->
<div class="side3 mar_b8">
	<div class="pagetitle">
		<h3>最近有谁来过...</h3>
	</div>
	<ul class="one">
	<!--{foreach $vistor_user_ids AS $one}-->
		<li class="hd"><a href="/{$vistors[$one]['nameUrl']}/" rel="contact"><em><img src="{$avatars[$vistors[$one]['idPicture']]}" class="buddy" icon="{$vistors[$one]['id']}" title="{$vistors[$one]['nameScreen']}" /></em>{$vistors[$one]['nameScreen']}</a></li>
	<!--{/foreach}-->
	</ul>
	<div class="clear"></div>
</div>
<!--{/if}-->
<!--{/if}-->
