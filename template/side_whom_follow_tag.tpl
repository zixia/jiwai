<!--${
	if (!$size) $size = 12;
	$follower_ids = JWTagFollower::GetFollowerIds( $tag['id'] , $size);
	$users = JWDB_Cache_User::GetDbRowsByIds( $follower_ids );
	$picture_ids = JWUtility::GetColumn($users, 'idPicture');
	$avatars = JWPicture::GetUrlRowByIds($picture_ids);
}-->
<!--{if count($users)}-->
<div class="side2">
	<div class="pagetitle">
		<h3 class="lt">最近加入关注... &nbsp;</h3>
		<div class="clear"></div>
	</div>
	<ul class="one">
	<!--{foreach $users AS $one}-->
		<li class="hd"><a href="/{$one['nameUrl']}/" title="{$one['nameScreen']}"><img src="{$avatars[$one['idPicture']]}" title="{$one['nameScreen']}" />{$one['nameScreen']}</a></li>
	<!--{/foreach}-->
		<div class="clear"></div>
	</ul>
	<div class="clear"></div>
</div>
<!--{/if}-->
