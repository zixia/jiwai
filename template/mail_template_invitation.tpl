<!--${
	$user = JWUser::GetUserInfo($g_current_user_id);
	$photo_url = JWPicture::GetUrlById($user['idPicture'], 'thumb96');
	$count = JWSns::GetUserState($user['id']);
	$invitecode = JWUser::GetIdEncodedFromIdUser($g_current_user_id);
	$friend_ids = JWFollower::GetFollowingIds( $g_current_user_id );
	$friend_ids = array_merge( array(34459), $friend_ids); //冷笑话
	if (count($friend_ids)>6) {
		$friend_ids = array_slice($friend_ids,0,6);
	}
	$friend_users = JWDB_Cache_User::GetDbRowsByIds($friend_ids);
	$photo_ids = JWUtility::GetColumn($friend_users, 'idPicture');
	$photo_urls = JWPicture::GetUrlRowByIds($photo_ids);
}-->
<html>
<body>
<style>
a{color:#F87A01; text-decoration:none}
a:hover{color:#F87A01; text-decoration:underline}
a.l{color:#F87A01; text-decoration:underline}
</style>
<table cellpadding="0" cellspacing="0"  style="width:700px; margin:0 auto;font-size:12px; line-height:2em;border:1px #ddd solid;padding:5px"><tr>
	<td width="120" align="center" valign="top">
		<div style="margin-right:20px;">
			<div style="width:90px; height:90px; overflow:hidden;padding:1px;border:1px #ddd solid">
				<a href="/{$user['nameUrl']}/" title="{$user['nameScreen']}"><img src="{$photo_url}" width="90" height="90" border="0" /></a>
			</div>
			<a href="/{$user['nameUrl']}/" title="{$user['nameScreen']}">{$user['nameScreen']}</a>
			<div style="color:#999; line-height:1.5em;">
				<div>{$count['status']} 条叽歪</div>
				<div>关注 {$count['following']} 人</div>
				<div>被 {$count['follower']} 人关注</div>
			</div>
		</div>
	</td>
	<td valign="top" style="font-size:14px;">
		<div >
			Hi，我是<strong>{$user['nameScreen']}</strong>（{$user['nameFull']}），我在<a href="http://jiwai.de" title="叽歪网">叽歪网</a>上建立了自己的碎碎念博客，用只言片语记录生活轨迹。<br />
			<br/>
			<div style="font-size:12px; line-height:1.5em">
				想知道我的最新动态？<br/>
				想通过QQ与MSN聊天？<br/>
				想用手机拍照上传照片随时随地的更新博客？<br/>
				想通过短信免费接收朋友的叽歪碎碎念？<br/>
				......<br/>
				请你赶紧加入叽歪网并关注我吧！<br/>
			</div>
			<div style="margin:50px auto;">
				请点击这里 <a href="http://jiwai.de/wo/invitations/i/{$invitecode}" style="font-size:16px;color:#333"><b>接受邀请</b></a>，注册后即可直接开始关注 {$user['nameScreen']}<br/>
				如果不能点击，请复制 <a href="http://jiwai.de/wo/invitations/i/{$invitecode}" class="l">http://jiwai.de/wo/invitations/i/{$invitecode}</a> 到地址栏，回车即可完成；
			</div>
		</div>
		<!--{if count($friend_users)}-->
		<div>
			<div style="margin:0 0 0.5em 0">你可能还会对这些人的叽歪感兴趣：</div>
			<table border="0" width="100%"><tr>
			<!--{foreach $friend_users AS $one}-->
				<td align=center><a href="/{$one['nameUrl']}/"><img src="{$photo_urls[$one['idPicture']]}" width="48" height="48" border="0"><br />{$one['nameScreen']}</a></td>
			<!--{/foreach}-->
			</tr></table>
		</div>
		<!--{/if}-->
		<div style="color:#999">
			<div style="margin:2em auto;font-size:12px; line-height:1.5em"> 注册后，你可以使用QQ、手机短信、手机彩信、MSN、Gtalk、飞信、Skype、网页、wap等方式免费的更新你的叽歪碎碎念博客，关注你的人可以通过这些方式即时的收到你的叽歪，你也可以通过这些方式即时的收到你所关注的人的叽歪。<br />
			意味着你可以通过各种方式与朋友相互叽歪哦！<br />
			</div>
		</div>
		<div style="margin-bottom:30px;">
			如果你暂时不想加入，也可以在这里关注我的最新动态，还可以直接回复与我交流哦！<br />
			<a href="http://jiwai.de/{$user['nameUrl']}/"title="{$user['nameScreen']}">http://jiwai.de/{$user['nameUrl']}/</a>

		</div>
	</td>
</tr></table>
</body>
</html>
