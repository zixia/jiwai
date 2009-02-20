<div class="block">
	<div class="aalgin">
	<!--${$words = JWTrackUser::GetWordListByHot(100, false)}-->
	<!--${$styles = array('ads', 'adb', 'ags', 'agb');}-->
	<!--{foreach $words AS $one}-->
	<!--${$style = $styles[($one['count']%4)];}-->
		<a href="/k/{$one['wordTerm']}/" title="{$one['wordTerm']}" class="{$style}">{$one['wordTerm']}</a>
	<!--{/foreach}-->
	</div>
</div>
<br/>
