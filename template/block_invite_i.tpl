<div class="usermsg mar_b8">
	<div class="lt">
		<div class="one">
			<div class="hd mar_b8">
				<a href="/{$g_page_user['nameUrl']}/"><img src="${JWPicture::GetUrlById($g_page_user['idPicture'],'thumb48')}" title="{$g_page_user['nameScreen']}" /></a>
			</div>
		</div>
	</div>
	<div class="msg">
		<h1>{$g_page_user['nameScreen']}&nbsp;希望在叽歪网上关注你</h1>
	</div>
	<div class="clear"></div>
</div>
<div class="block">
	<div class="mar_b20">
		<div class="mar_b8">注册后，你可以使用QQ、手机短信、手机彩信、MSN、Gtalk、Skype、网页、wap等方式免费的更新你的叽歪碎碎念博客，你的朋友可以通过这些方式即时的收到你的叽歪，你也可以通过这些方式即时的收到你朋友的叽歪。</div>
		<div>意味着你可以通过各种方式与朋友相互叽歪哦！</div>
	</div>
	<div class="mar_b20">
		<div class="rt dark">&gt;&gt; <a href="/wo/invitations/destroy/{$invite_code}">不用了,谢谢！</a></div>
		<div class="button side_btn bbtn">
			<div class="at"></div><div class="bt"></div>
			<div class="tt" onclick="location.href='/wo/invitations/accept/{$invite_code}';">接受邀请</div>
			<div class="bt"></div><div class="at"></div>
		</div>
	</div>
	<div class="mar_b50 f_14">如果已经有叽歪帐号了，请<a href="/wo/login">直接登录</a>，登陆后将自动接受这份邀请。</div>
	<div>
		<div class="mar_b8">
			<div class="pagetitle">
				<h3>{$g_page_user['nameScreen']}关注的人...</h3>
			</div>
		</div>
		<ul class="one one_block">
		<!--{foreach $friend_users AS $one}-->
			<li class="hd"><a href="/{$one['nameUrl']}/" title="{$one['nameScreen']}"><em><img src="{$picture_urls[$one['idPicture']]}" alt="{$one['nameScreen']}" title="{$one['nameScreen']}" class="buddy" icon="{$one['id']}" /></em>{$one['nameScreen']}</a></li>
		<!--{/foreach}-->
		</ul>
	</div>
</div>
<div class="clear"></div>
