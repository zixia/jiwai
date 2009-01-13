<ul class="one one_block">
<!--{foreach $friend_ids AS $user_id}-->
<!--${
	$index = (++$index) % 5;
	$user_row = $friend_user_rows[$user_id];
	$user_action_row = $user_action_rows[$user_id];
	$user_picture_id = @$user_row['idPicture'];
	$user_icon_url = JWTemplate::GetConst('UrlStrangerPicture');
	if ( $user_picture_id )
		$user_icon_url = $picture_url_row[$user_picture_id];
}-->
	<li class="hd"><a href="/{$user_row['nameUrl']}/" title="{$user_row['nameScreen']}"><img src="{$user_icon_url}" alt="{$user_row['nameScreen']}" title="{$user_row['nameScreen']}" class="buddy" icon="{$user_row['id']}" /> {$user_row['nameScreen']}</a></li>
<!--{/foreach}-->
</ul>
