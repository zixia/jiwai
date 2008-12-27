<!--{if !$g_current_user_id}-->
<div class="pagetitle">
	<h1>叽歪成员搜索结果</h1>
</div>
<!--{/if}-->
 <!--{if count($searched_users)}-->
 <ul class="one one_block">
	<!--{foreach $searched_users AS $oneuser}-->
	<li class="hd"><a href="/{$oneuser['nameUrl']}" title="{$oneuser['nameScreen']}"><em><img src="{$picture_urls[$oneuser['idPicture']]}" alt="{$oneuser['nameScreen']}" title="{$oneuser['nameScreen']}" /></em>{$oneuser['nameScreen']}</a></li>
	<!--{/foreach}-->
</ul>
<!--{/if}-->
