<div class="pagetitle">
	<h1>搜索结果</h1>
</div>
<div class="mar_b8">共有 <b>{$count}条</b> 符合 {$trackword}</div>
<!--{if $guessword}-->
<div>你是不是要搜索：<!--{foreach $guessword AS $one}--><span class="f_yel"><a href="/wo/search/statuses?q={$one}">{$one}</a></span>&nbsp;<!--{/foreach}--></div>
<!--{else}-->
<div class="mar_b20 bg_yel pad_3">
	<ul class="dot_b">
		<li>点击进入单个词汇页面，可以开始追踪或取消追踪该词汇</li>
		<li>追踪成功后，你将可以使用所绑定的聊天软件，开始实时的接收含有这个词汇的叽歪，是不是很酷 :)</li>
	</ul>
</div>
<!--{/if}-->
<!--${$o=$sovalue['o']=='asc'?'desc':'asc';}-->
<div class="txt_r">
<!--{if $sovalue['f']=='null'}-->		
	<a href="{$sourl}&f=time" class="f_gra">↓↑按时间排序</a>
	<a href="{$sourl}&f=null&o={$o}" class="f_gra bg_pink">↓↑按相关度排序</a>
<!--{else}-->
	<a href="{$sourl}&f=time&o={$o}" class="f_gra bg_pink">↓↑按时间排序</a>
	<a href="{$sourl}&f=null" class="f_gra">↓↑按相关度排序</a>
<!--{/if}-->
</div>
