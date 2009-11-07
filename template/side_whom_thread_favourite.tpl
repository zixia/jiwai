<!--${
	$favourite_data = JWFavourite::GetBeFavouriteData($thread_id);
	$user_ids = $favourite_data['user_ids'];
	$user_ids = JWRemote::GetActivateUserId($user_ids, $size);
	$users = JWDB_Cache_User::GetDbRowsByIds($user_ids);
	$avatars = JWFunction::GetColArrayFromRows($users,'idPicture');
	$avatars = JWPicture::GetUrlRowByIds($avatars);

	$is_favourited = JWFavourite::IsFavourite($g_current_user_id, $thread_id);
}-->
<div class="side3">
	<div class="pagetitle">
		<h2><!--{if !$is_favourited}--><a href="/wo/favourites/create/{$thread_id}">收藏这条叽歪</a><!--{else}--><a>已收藏</a><!--{/if}-->收藏</h2>
	</div>
	<!--{if count($users)}-->
	他们收藏了这条叽歪
	<!--{else}-->
	还没有人收藏这条叽歪
	<!--{/if}-->

	<!--${$index=0}-->
	<!--{foreach $users AS $one}-->
	<div class="imglist"><a href="/{$one['nameUrl']}/" rel="contact"><img src="{$avatars[$one['idPicture']]}" class="buddy_icon" icon="{$one['id']}"/></a></div>
	<!--{if (++$index%4)==0 || $index==count($users)}-->
	<div class="clear"></div>
	<!--{/if}-->
	<!--{/foreach}-->
</div>
