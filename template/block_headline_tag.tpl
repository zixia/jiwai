<script>
window.jiwai_init_hook_tagupdater = function(){
	$('jw_rtid').value = '{$tag['id']}';
}
</script>
<div class="f">
	<form name="" action="/wo/status/update" id="updaterForm" method="post" onsubmit="$('jw_status').style.backgroundColor='#eee';">
		<div class="pagetitle">
		<!--{if $tipnote}-->
			<h1>{$tipnote}</h1>
		<!--{else}-->
			<h1>这里是&nbsp;[{$tag['name']}]&nbsp;话题叽歪</h1>
		<!--{/if}-->
		</div>
		<!--{include block_updater}-->
	</form>
	<div class="clear"></div>
</div>
