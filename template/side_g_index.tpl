<div class="side1">
	<div class="bg_pink pad">
		<div class="gra_input"><form action="/wo/search/users">
			<input type="text" name="q" value="QQ MSN Email id..." onclick="if(this.value=='QQ MSN Email id...')this.value='';" onblur="if(this.value=='')this.value='QQ MSN Email id...';" class="inp_w" /> <input type="button" value="谁在叽歪" class="def_btn" onclick="this.form.submit();" /></form>
		</div>
	</div>
</div>
<div class="side2">
	<div class="pagetitle">
		<h3 class="lt">近期活动... &nbsp;</h3> <div class="lightbg f_gra lt">（<a href="/g/es">全部</a>）</div>
		<div class="clear"></div>
	</div>
	<div class="bg_pink pad">
	<!--${$events = JWFarrago::GetEScreen(3);}-->
	<!--{foreach $events AS $one}-->
		<div class="one">
			<div><b>{$one['event']}</b></div>
			<div class="lt hd"><a href="/{$one['user']['nameUrl']}/"><img src="${JWPicture::GetUrlById($one['user']['idPicture'],'thumb48');}" class="buddy" icon="{$one['user']['id']}" /></a></div>
			<div class="dark">
				<div>时间：{$one['time']}</div>
				<div>地点：{$one['address']}</div>
				<div><span class="lightbg rt"><a href="/wo/action/follow/{$one['user']['id']}" onclick="return JWAction.follow({$one['user']['id']});">关注</a></span> 活动ID：<a href="/{$one['user']['nameUrl']}/">{$one['user']['nameScreen']}</a></div>
			</div>
			<div class="line mar_b8">&nbsp;</div>
		</div>
	<!--{/foreach}-->
	</div>
</div>
<div class="side2">
	<div class="pagetitle">
		<h3>热门叽歪... &nbsp;</h3>
	</div>
	<div class="bg_pink pad">
	<!--${
		$hotjiwais = JWFarrago::GetHotJiWai(3);
		$user_ids = JWUtility::GetColumn($hotjiwais, 'idUser');
		$users = JWDB_Cache_User::GetDbRowsByIds($user_ids);
	}-->
	<!--{foreach $hotjiwais AS $one}-->
		<div class="one">
			<div class="lt hd">
				<a href="/{$users[$one['idUser']]['nameUrl']}/"><img src="${JWPicture::GetUrlById($users[$one['idUser']]['idPicture']);}" class="buddy" icon="{$one['idUser']}" /><br />{$users[$one['idUser']]['nameScreen']}</a>

			</div>
			<div>{$one['status']}</div>
			<div class="txt_r lightbg"><a href="/{$users[$one['idUser']]['nameUrl']}/thread/{$one['id']}">${JWDB_Cache_Status::GetCountReply($one['id'])}条回复</a></div>
			<div class="line mar_b8 clear">&nbsp;</div>
		</div>
	<!--{/foreach}-->
	</div>
</div>
<div class="side2">
	<div class="pagetitle">
		<h3>精彩照片... &nbsp;</h3>
	</div>
	<!--${
		$u = JWUser::GetUserInfo($photo['idUser']);
		$presult = JWPlugins::GetPluginResult($photo);
	}-->
	<div class="bg_pink pad">
		<div class="mar_b8">
			<div class="side_picture">{$presult['html']}</div>
		</div>
		<div>
			<div class="mar_b8"><h3><a href="/{$u['nameUrl']}/">{$u['nameScreen']}</a></h3></div>
			<div>{$photo['status']}</div>
			<div class="txt_r lightbg"><a href="/{$u['nameUrl']}/thread/{$photo['id']}">回复</a></div>
		</div>
		<div class="clear"></div>
	</div>
	<!--{/foreach}-->
	<div class="txt_r pad_t8"><a href="http://help.jiwai.de/MMS" class="f_gra_l">如何发送照片？</a></div>
</div>
<div class="side2">
	<div class="pagetitle">
		<h3>精彩视频... &nbsp;</h3>
	</div>
	<!--${$presult=JWPlugins::GetPluginResult($video);}-->
	<div class="bg_pink pad">
		<div class="mar_b8">
			<div class="side_video">{$presult['html']}</div>
		</div>
		<div>
			<div class="mar_b8"><h3><a href="/youkuhot/">YoukuHot</a></h3></div>
			<div>{$video['status']}</div>
			<div class="txt_r lightbg"><a href="/youkuhot/thread/{$video['id']}">回复</a></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="txt_r pad_t8"><a href="http://blog.jiwai.de/index.php/archives/25" class="f_gra_l">如何发送视频？</a></div>
</div>

<div class="side3">
	<h4 class="mar_b8">&gt;&gt;<a href="/public_timeline/"> 最新叽歪</a></h4>
	<h4>&gt;&gt;<a href="http://lab.jiwai.de/googlemap/"> 叽歪大中国</a></h4>
</div>
<!--{include side_registeruser}-->
