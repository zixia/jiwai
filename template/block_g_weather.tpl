<div class="pagetitle">	
	<h1>叽歪天气预报</h1>
</div>
<ul class="one one_block">
<!--{foreach $weather_users AS $one}-->
	<li class="hd"><a href="/{$one['nameUrl']}/" title="{$one['nameScreen']}"><em><img src="{$picture_urls[$one['idPicture']]}" alt="{$one['nameScreen']}" title="{$one['nameFull']}" /></em>{$one['nameScreen']}</a></li>
<!--{/foreach}-->
</ul>
<div class="clear"></div>
