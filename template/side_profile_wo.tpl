<!--${
	$devices = JWDevice::GetDeviceRowByUserId($g_page_user_id);
	$user = JWUser::GetUserInfo($g_page_user_id);

	if ( !empty($user['url']) )
	{
		$url = $user['url'];

		if ( !preg_match('/^\w+:\/\//',$url) )
			$url = 'http://' . $url;

		$show_url = JWStatus::GetSubString($url, 30);
		$url = htmlspecialchars($url);
		$show_url = htmlspecialchars($show_url);
	}

	if ( JWUser::IsAnonymous($user['id']) )
	{
		$user['bio'] = '这是一个IP漂流瓶用户。他是由很多匿名用户组成的，因为他们都有着共同的IP段，于是便汇聚在了一起，你看到的是这个瓶子里所有人的叽歪。[<a href="http://help.jiwai.de/SeagoingBottles" target="_blank">查看详情</a>]';
	}

	$specials = array('nameFull', 'bio', 'interest', 'bookWriter', 'player', 'music', 'artist');
	foreach( $specials as $special)
		$user[$special] = htmlspecialchars( $user[$special] );

		$aDeviceInfo_rows = JWDevice::GetDeviceRowByUserId($aUserInfo['id']);

		$isUserLogined = !empty($g_current_user_id);
		$imicoUrlSms = "/wo/devices/sms";
		$imicoUrlIm = "/wo/devices/im";
		$imicoUrlHelpSms = "http://help.jiwai.de/VerifyYourPhone";
		$imicoUrlHelpIm = "http://help.jiwai.de/VerifyYourIM";
}-->
<div class="side1 mar_b8">
	<div class="pagetitle">
		<h3>个人资料</h3>
	</div>
	<div class="icon">
	<!--{foreach $devices AS $type=>$one}-->
	<!--${if ($one['secret']) continue;}-->
	<!--${$imicoUrlHref = $isUserLogined ?
		( $type!='sms' ? $imicoUrlIm : $imicoUrlSms ) 
		: 
		( $type!='sms' ? $imicoUrlHelpIm : $imicoUrlHelpSms );
		$typename = JWDevice::GetNameFromType( $type );
		$alt = "已绑定 $typename";
	}-->
	<a href="{$imicoUrlHref}" class="ico_{$type}" alt="{$alt}" title="{$alt}"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="16" height="16"/></a>
	<!--{/foreach}-->
	</div>
	<ul>
	<!--{if !empty($user['nameFull'])}-->
	<li>名字：{$user['nameFull']}</li>
	<!--{/if}-->
	<!--{if in_array($user['gender'],array('female','male'))}-->
	<li>性别：{$_INI['gender'][$user['gender']]}</li>
	<!--{/if}-->
	<!--{if !empty($user['location'])}-->
	<li>位置：${JWLocation::GetLocationName($user['location'])}</li>
	<!--{/if}-->
	<!--{if !empty($user['url'])}-->
	<li>网站：<a href="{$url}" rel="me" target="_blank">{$show_url}</a></li>
	<!--{/if}-->
	<!--{if !empty($user['bio'])}-->
	<li>自述：{$user['bio']}</li>
	<!--{/if}-->
	<!--{if !empty($user['interest'])}-->
	<li>兴趣爱好：{$user['interest']}</li>
	<!--{/if}-->
	<!--{if !empty($user['bookWriter'])}-->
	<li>喜欢的书和作者：{$user['bookWriter']}</li>
	<!--{/if}-->
	<!--{if !empty($user['player'])}-->
	<li>喜欢的电影和演员：{$user['player']}</li>
	<!--{/if}-->
	<!--{if !empty($user['music'])}-->
	<li>喜欢的音乐和歌手：{$user['music']}</li>
	<!--{/if}-->
	<!--{if !empty($user['place'])}-->
	<li>喜欢的地方：{$user['place']}</li>
	<!--{/if}-->
	</ul>
</div>
