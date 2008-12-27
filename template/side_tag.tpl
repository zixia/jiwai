<!--${
	$tags = JWDB_Cache_Tag::GetDbRowsByIds($tag_ids);
}-->
<div class="side2">
	<div class="pagetitle">
		<h2>标签云</h2>
	</div>
	<!--${$index=0}-->
	<!--{foreach $tags AS $one}-->
	<a href="/t/{$one['name']}/">{$one['name']}</a>
	<!--{/foreach}-->
</div>
