<div class="pagetitle">
	<h1>叽歪大屏幕活动</h1>
</div>
<div class="block">
	<div class="mar_b50">
		<div>
			<!--{foreach $events AS $one}-->
			<div class="one">
				<div><b>{$one['event']}</b></div>
				<div class="lt hd"><a href="/{$one['user']['nameUrl']}"><img class="buddy" src="{$picture_urls[$one['user']['idPicture']]}" /></a></div>
				<div class="dark">
					<div>时间：<span class="f_gra">{$one['time']}</span></div>
					<div>地点：<span class="f_gra">{$one['address']}</span></div>
					<div>活动ID：<a href="/{$one['user']['nameUrl']}">{$one['user']['nameScreen']}</a></div>
				</div>
				<div align="right" class="lightbg"><a href="/wo/action/follow/{$one['user']['id']}" onclick="return JWAction.follow({$one['user']['id']});">关注</a></div>
				<div class="line mar_b8"></div>
			</div>
			<!--{/foreach}-->
		</div>
	</div>
</div>
