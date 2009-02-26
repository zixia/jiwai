<div class="block">
	<div class="aalgin">
	<!--${$words = JWFarrago::GetHotWords(100)}-->
	<!--${$styles = array('ads', 'adb', 'ags', 'agb');}-->
	<!--{foreach $words AS $one}-->
	<!--${$style = $styles[($one['count']%4)];}-->
		<a href="/t/{$one['name']}/" title="{$one['name']}" class="{$style}">{$one['name']}</a>
	<!--{/foreach}-->
	</div>
</div>
<br/>
