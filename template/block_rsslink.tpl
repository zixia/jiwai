<div class="rss">
	<span class="ico_rss">
		<img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="18" height="12" />
	</span>
<!--{if $g_page_on}-->
	| <a href="http://api.jiwai.de/statuses/user_timeline/{$g_page_user_id}.rss">订阅{$g_page_user['nameScreen']}</a>
<!--{/if}-->
<!--{if $tag}-->
	| <a href="http://api.jiwai.de/statuses/channel_timeline/{$tag['id']}.rss">订阅[{$tag['name']}]</a>
<!--{/if}-->
<!--{if (!$g_page_on && $g_current_user_id)}-->
	| <a href="http://api.jiwai.de/statuses/user_timeline/{$g_current_user_id}.rss">我的RSS源</a>
<!--{/if}-->
</div>
<div class="clear"></div>
