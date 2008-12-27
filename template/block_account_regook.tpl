<div id="set_bor">
	<div class="mar_b20 f_14">一切就绪了，你可以：</div>
	<ul class="mar_b20">
		<li class="f_14">&gt;&gt; &nbsp;<a href="/wo/invite/">找朋友</a></li>
		<li class="f_gra indent">找找看你经常联系的好友，可能他们已经在叽歪了哦~</li>
	</ul>
	<ul class="mar_b20">
		<li class="f_14">&gt;&gt;  &nbsp;<a href="/wo/devices/sms">绑定设置</a></li>
		<li class="f_gra indent">你除了可以使用网页进行叽歪，还可以使用QQ、手机短信、手机彩信、MSN、Gtalk、Skype等方式免费的更新你的叽歪碎碎念博客。</li>
	</ul>
	<ul class="mar_b20">
		<li class="f_14">&gt;&gt;  &nbsp;<a href="/wo/">马上开始叽歪</a></li>
		<li class="f_gra indent">赶紧告诉大家你现在在做什么，开始记录生活中的点滴吧~</li>
	</ul>
	<ul class="mar_b50">
		<li class="f_14">&gt;&gt;  &nbsp;<a href="/public_timeline/">随便逛逛</a></li>
		<li class="f_gra indent">看看大家都在叽歪什么~</li>
	</ul>
	<div>
		<div class="mar_b20 f_14 ">或者，看看他们在叽歪什么</div>
		<ul class="one one_block">
		<!--{foreach $featured_users AS $uid=>$one}-->
			<li class="hd"><a href="/{$one['nameUrl']}" title="{$one['nameScreen']}"><em><img src="{$picture_urls[$one['idPicture']]}" alt="{$one['nameScreen']}" title="{$one['nameScreen']}" /></em>{$one['nameScreen']}</a></li>
		<!--{/foreach}-->
		</ul>
	</div>
</div>
<div class="clear"></div>
