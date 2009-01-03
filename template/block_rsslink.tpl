<!--${$rsslink = JWUtility::GetRssLink();}-->
<div class="rss">
	<span class="ico_rss">
		<img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="18" height="12" />
	</span>
<!--{foreach $rsslink AS $onerss=>$onetitle}-->
	| <a href="{$onerss}">{$onetitle}</a>
<!--{/foreach}-->
</div>
<div class="clear"></div>
