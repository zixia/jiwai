<div class="pagetitle">
	<h1>搜索结果</h1>
</div>
<div class="f_14">共有 <b>{$count}条</b> 符合</div>
<!--${$o=$sovalue['o']=='asc'?'desc':'asc';}-->
<div class="txt_r">
<!--{if $sovalue['f']}-->		
	<a href="{$sourl}" class="f_gra">↓↑按相关度排序</a>
	<a href="{$sourl}&f=time&o={$o}" class="f_gra bg_pink">↓↑按时间排序</a>
<!--{else}-->
	<a href="{$sourl}&o={$o}" class="f_gra bg_pink">↓↑按相关度排序</a>
	<a href="{$sourl}&f=time" class="f_gra">↓↑按时间排序</a>
<!--{/if}-->
</div>
