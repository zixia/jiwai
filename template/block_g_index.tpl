<div class="pagetitle">
	<h1>欢迎你到叽歪网随便逛逛</h1>
</div>
<div class="block">
	<div class="mar_b50">
		<div><a href="/g/weather" class="rt">其他城市</a><span class="dark"><a href="/北京天气/">北京天气</a></span>&nbsp;${htmlspecialchars($weather['status'])}</div>
	</div>
	<div class="mar_b50">
		<div class="pagetitle">
			<h3>大家的话题...</h3>
		</div>
		<div class="aalgin">
		<!--${$words = JWFarrago::GetHotWords()}-->
		<!--{foreach $words AS $one}-->
			<a href="/t/{$one[0]}/" title="{$one[0]}" class="{$one[1]}">{$one[0]}</a>
		<!--{/foreach}-->
		</div>
	</div>
	<div class="mar_b50">
		<div class="pagetitle">
			<h3>新闻资讯...</h3>
		</div>
		<ul class="mar_b20">
		<!--${$program_u=JWUser::GetUserInfo($program['idUser'])}-->
			<li><span class="rt"><a href="/g/program">更多电视台</a></span><span class="dark"><a href="/{$program_u['nameUrl']}/">{$program_u['nameScreen']}</a></span>&nbsp;${mb_strimwidth(strip_tags($program['status']),0,66,'...')}</li>
			<li><span class="rt"><a href="/g/astro">更多星座</a></span>		<span class="dark"><a href="/{$astro_user['nameUrl']}/">{$astro_user['nameScreen']}</a></span>&nbsp;${mb_strimwidth(strip_tags($astro['status']),0,66,'...')}</li>
		</ul>
		<ul class="channel_g">
		<!--{foreach $newsbot AS $newsc=>$one}-->
			<li><span><b>{$newsc} </b>&nbsp;|</span><!--{foreach $one AS $oone}--> <span class="dark"><a href="/{$oone}/">{$oone}</a></span><!--{/foreach}--></li>
		<!--{/foreach}-->
		</ul>
	</div>
	<div class="mar_b50">
		<div class="pagetitle">
			<h3>人气用户...</h3>
		</div>
	<!--{foreach $hotids AS $one}-->
	<!--${
		$u = JWUser::GetUserInfo($one);
		$s = JWStatus::GetHeadStatusRow($u['id']);
	}-->
		<div class="one">
			<div class="lt"><a href="/{$u['nameUrl']}/"><img src="${JWPicture::GetUrlById($u['idPicture']);}" width="32" height="32"  /></a></div>
			<div class="ct line">
				<div class="g_rt">
					<span class="lightbg"><a href="/{$u['nameUrl']}/thread/{$s['id']}">回复</a></span>
				</div>
				<div class="dark"><a href="/{$u['nameUrl']}/">{$u['nameScreen']}</a> <a href="/{$u['nameUrl']}/statuses/{$s['id']}" class="bla" title="${strip_tags($s['status'])}">${mb_strimwidth(strip_tags($s['status']),0,60,'...')}</a> </div>
			</div>
			<div class="clear"></div>
		</div>
	<!--{/foreach}-->
	</div>

	<div class="mar_b50">
		<div class="pagetitle">
			<h3>推荐用户...</h3>
		</div>
	<!--{foreach $featureds AS $one}-->
	<!--${
		$u = JWUser::GetUserInfo($one);
		$s = JWStatus::GetHeadStatusRow($u['id']);
	}-->
		<div class="one">
			<div class="lt"><a href="/{$u['nameUrl']}/"><img src="${JWPicture::GetUrlById($u['idPicture']);}" width="32" height="32"  /></a></div>
			<div class="ct line">
				<div class="g_rt">
					<span class="lightbg"><a href="/{$u['nameUrl']}/thread/{$s['id']}">回复</a></span>
				</div>
				<div class="dark"><a href="/{$u['nameUrl']}/">{$u['nameScreen']}</a> <a href="/{$u['nameUrl']}/statuses/{$s['id']}" class="bla" title="${strip_tags($s['status'])}">${mb_strimwidth(strip_tags($s['status']),0,60,'...')}</a> </div>
			</div>
			<div class="clear"></div>
		</div>
	<!--{/foreach}-->
	</div>

	<div class="mar_b50">
		<div class="pagetitle">
			<h3>看看新来的...</h3>
		</div>
	<!--{foreach $newids AS $one}-->
	<!--${
		$u = JWUser::GetUserInfo($one);
		$s = JWStatus::GetHeadStatusRow($u['id']);
	}-->
		<div class="one">
			<div class="lt"><a href="/{$u['nameUrl']}/"><img src="${JWPicture::GetUrlById($u['idPicture']);}" width="32" height="32"  /></a></div>
			<div class="ct line">
				<div class="g_rt">
					<!--{if $s}-->
					<span class="lightbg"><a href="/{$u['nameUrl']}/thread/{$s['id']}">回复</a></span>
					<!--{else}-->
					<span class="lightbg"><a href="/wo/action/nudge/{$one}" onclick="return JWAction.redirect(this);">挠挠</a></span>
					<!--{/if}-->
				</div>
				<div class="dark"><a href="/{$u['nameUrl']}/">{$u['nameScreen']}</a>&nbsp;<!--{if $s}--><a href="/{$u['nameUrl']}/statuses/{$s['id']}" class="bla" title="${strip_tags($s['status'])}">${mb_strimwidth(strip_tags($s['status']),0,60,'...')}</a><!--{else}--><a href="/wo/action/nudge/{$one}" class="bla" onclick="return JWAction.redirect(this);">我还没有叽歪过~~挠我一下吧~~<!--{/if}--></a> </div>
			</div>
			<div class="clear"></div>
		</div>
	<!--{/foreach}-->
	</div>

	<div class="mar_b50">
		<div class="pagetitle">
			<h3>叽歪达人...</h3>
		</div>
	<!--{foreach $darens AS $dc=>$done}-->
	<!--${
		$u = JWUser::GetUserInfo($done[1][0]['id']);
		$s = JWStatus::GetHeadStatusRow($u['id']);
	}-->
		<div class="one">
			<div class="mar_b8"><a href="#" class="ico_{$done[0]}"><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="20" height="16" /></a> <b>{$dc} 共{$done[1][0]['count']}条</b></div>
			<div class="lt"><a href=""><img src="${JWPicture::GetUrlById($u['idPicture'])}" width="32" height="32" /></a></div>
			<div class="ct line">
				<div class="g_rt">
					<span class="lightbg"><a href="/{$u['nameUrl']}/thread/{$s['id']}">回复</a></span>
				</div>
				<div class="dark"><a href="/{$u['nameUrl']}/">{$u['nameScreen']}</a> <a href="/{$u['nameUrl']}/statuses/{$s['id']}" class="bla" title="${strip_tags($s['status'])}">${mb_strimwidth(strip_tags($s['status']),0,60,'...')}</a></div>
			</div>
			<div class="clear"></div>
		</div>
	<!--{/foreach}-->
	</div>
</div>
<div class="clear"></div>
