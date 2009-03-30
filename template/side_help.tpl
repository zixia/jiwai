<!--${$b = JWTagFollower::GetTagUser($tag['id'], $g_current_user_id);}-->
<div class="side1">
	<div class="pagetitle">
	<!--{if $tag}-->
		<h3 class="mar_b8"><a href="/wo/feedback/index/1">信息不通？</a></h3>
		<h3 class="mar_b8"><a href="/wo/feedback/index/2">举报用户？</a></h3>
		<div class="lightbg"><a href="/wo/action/<!--{if $b}-->toff<!--{else}-->tfollow<!--{/if}-->/{$tag['id']}"><!--{if $b}-->取消<!--{/if}-->关注[帮助留言板]</a></div>
	<!--{else}-->
		<h3 class="mar_b8"><a href="/t/帮助留言板/">帮助留言板</a></h3>
	<!--{/if}-->
	</div>
</div>
<div class="side3">
	<div class="pagetitle">
		<h3>是否要问以下的问题呢？</h3>
	</div>
	<ul class="dark mar_b20">
		<li><a href="http://help.jiwai.de/Faq" target="_blank">常见问题集合(FAQ)</a><br></li>
		<li><a href="http://help.jiwai.de/MobileFAQ" target="_blank">手机常见问题</a></li>
		<li><a href="http://help.jiwai.de/IMFAQ" target="_blank">QQ、MSN、Gtalk常见问题</a></li>
		<li><a href="http://help.jiwai.de/VerifyYourIM" target="_blank">如何绑定QQ、MSN、Gtalk？</a></li>
		<li><a href="http://help.jiwai.de/VerifyYourPhone" target="_blank">如何绑定手机？</a></li>
		<li><a href="http://help.jiwai.de/MakeFriend" target="_blank">如何关注别人？</a></li>
		<li><a href="http://help.jiwai.de/WhatistheRepliestab" target="_blank">如何回复别人？</a></li>
		<li><a href="http://help.jiwai.de/WhatisaFavorite" target="_blank">如何收藏感兴趣的叽歪？</a></li>
		<li><a href="http://help.jiwai.de/NotificationsSelection" target="_blank">如何用手机和QQ等收到别人的叽歪？</a></li> 
	<li><a href="http://help.jiwai.de/HowToAddWidgetIntoYourBlogs" target="_blank">如何在博客上显示我的叽歪？</a> </li>
	</ul>
	<div class="pagetitle">
		<h4 class="mar_b8">&gt;&gt; <a href="http://help.jiwai.de/">帮助中心</a></h4>
	</div>
</div>
