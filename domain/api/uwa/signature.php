<?php
require_once(dirname(__FILE__) . "/../../../jiwai.inc.php");
echo '<?xml version="1.0" encoding="UTF-8" ?>';
$u = empty($_GET['u']) ? '' : $_GET['u'];
$rev = '{$Rev: 1954 $}';
$rev = preg_replace('#^[^\d]+(\d+)[^\d]+#', '$1', $rev);
if ($u) {
$user = JWUser::GetUserInfo($u);
$u = $user['nameScreen'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:widget="http://www.netvibes.com/ns/">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $u ? $user['nameFull'].'('.$u.')' : '叽歪 IM 签名'; ?></title>
<link rel="icon" type="image/x-icon" href="http://asset.jiwai.de/img/favicon.ico" />
<meta name="author" content="叽歪<?php echo $u ? '的'.$u : '网'; ?>" />
<meta name="website" content="http://jiwai.de/<?php echo $u ? $user['nameUrl'] : ''; ?>" />
<meta name="description" content="<?php echo $u ? $user['nameFull'].' '.str_replace(array("\r","\n"), ' ', htmlspecialchars($user['bio'])).' - Powered by 叽歪网' : '在个人空间和博客上显示你的MSN/Gtalk/QQ/Skype签名档'; ?>" />
<meta name="version" content="1.1.3.<?php echo $rev; ?>" />
<meta name="keyword" content="<?php echo $u ? $user['nameFull'].' '.$u.' ' : ''; ?>叽歪 JiWai 彩信 微博客 miniblog microblog im 签名 msn gtalk qq skype" />
<meta name="keywords" content="<?php echo $u ? $user['nameFull'].' '.$u.' ' : ''; ?>叽歪 JiWai 彩信 微博客 miniblog microblog im 签名 msn gtalk qq skype" />
<meta name="screenshot" content="<?php echo $u ? JWPicture::GetUserIconUrl($user['id'],'picture') : 'http://asset.jiwai.de/gadget/uwa/sow_screenshot.png'; ?>" />
<meta name="thumbnail" content="<?php echo $u ? JWPicture::GetUserIconUrl($user['id'], 96) : 'http://asset.jiwai.de/gadget/uwa/sow_thumbnail.png'; ?>" />
<meta name="debugMode" content="false" />
<meta name="autoRefresh" content="300" />
<widget:preferences>
	<preference type="text" label="你的叽歪登录名" name="screenName" defaultValue="<?php echo $u; ?>"/>
	<preference type="list" label="选择即时通讯软件" name="device" >
		<option value="msn-signature" label="MSN" />
		<option value="qq-signature" label="QQ" />
		<option value="gtalk-signature" label="GTalk" />
		<option value="skype-signature" label="Skype" />
	</preference>
</widget:preferences>
<script type="text/javascript">
<![CDATA[
function formatStatus(s) {
	return s.replace(/http:\/\/([\w\-\.]+)([^\s]+)/, '<a target="_blank" href="http://$1$2" class="extlink">$1</a>')
			.replace(/\[([^\]]+)\]/, '[<a target="_blank" href="http://jiwai.de/t/$1/">$1</a>]');
}
function formatDate(s) {
	var d = new Date(s.replace(/(\w+)\s+(\w+)\s+(\w+)\s+([\w:]+)\s+([\w+-]+)\s+(\w+)/, '$1 $2 $3 $6 $4 $5'));
	return (d.toLocaleDateString() == (new Date).toLocaleDateString()) 
		? d.toLocaleTimeString().replace(/(\d+):(\d+):(\d+)/, '$1:$2') 
		: d.toLocaleDateString();
}
var jw_footer = '';
widget.onLoad = function() {
	var screenName = widget.getValue("screenName");
	var device = widget.getValue("device");
	var withFriends = true;
	var numDisplay = 1;
	var updateBox = false;
	var uri;
	if (numDisplay<1) numDisplay = 1;
	if (numDisplay>20) numDisplay = 20;
	if (screenName == null || screenName == '') {
		jw_footer = '<p class="alert">现在显示的是叽歪广场。设置你的叽歪帐号后就可以在这里显示你MSN/QQ/GTalk/Skype签名了。还没有叽歪帐号？<a href="http://jiwai.de/wo/account/create" target="_blank">这里注册</a></p>'
		widget.setBody(jw_footer);
		uri = 'http://api.jiwai.de/statuses/public_timeline.json?count=5';
		withFriends = true;
	} else {
		widget.setTitle(screenName+'的'+device.replace(/\-.+$/, '')+'签名');
		uri = 'http://api.jiwai.de/statuses/user_timeline/' + encodeURIComponent(screenName) + '.json?count='+numDisplay+'&device='+device;
		jw_footer = updateBox ? '<form target="_blank" style="text-align:center;" action="http://jiwai.de/wo/status/update" method="post" onsubmit="var s=this.getElementsByName(\'jw_status\')[0]; if (s.value==\'\') return false; else s.value=\'@'+screenName+' \'+s.value;"><textarea name="jw_status" rows="3"></textarea><br /><input style="border:1px;" type="submit" value="留言"/> <a target="_blank" href="http://jiwai.de/wo/account/create">注册叽歪</a><input type="hidden" value="'+screenName+'" name="idUserReplyTo"/></form>' : '';
	}
	UWA.Data.getJson(uri, function(response){
		if (response == null) {
			widget.setBody('<p class="alert">无效数据</p>');
			return null;
		}
		var html = '';
		var l = 0;
		for (var i in response) {
			var o = response[i];
			l++;
			try{
				html += '<div style="padding-bottom:4px;margin-top:4px;border-bottom:solid 1px #eee;position:relative;" onmouseover="var e=this.getElementsByTagName(\'span\'); e=e[e.length-1]; UWA.$element(e).show();" onmouseout="var e=this.getElementsByTagName(\'span\'); e=e[e.length-1]; UWA.$element(e).hide();">'
				+ '<span><a target="_blank" href="'
				+ o.user.profile_url + '"><img style="float:right;width:24px;height:24px;" src="'
				+ o.user.profile_image_url + '"/></a>' //align="right"
				+ (withFriends ? '</span><span><a target="_blank" href="'+o.user.profile_url+'">' + o.user.screen_name + '</a>: ' : '')
				+ '</span><span>' + formatStatus(o.text)
				+ '</span> <span style="color:lightgrey">' + formatDate(o.created_at)
				+ '</span>' + (o.mms_image_url ? '<br /><a href="'+o.user.profile_url+'mms/'+o.id+'" target="_blank"><img src="'+o.mms_image_url+'" width="120" align="bottom"/></a>' : '')
				+ '<span style="position:absolute;right:0px;display:none;"><a style="background:#eee" target="_blank" href="'+o.user.profile_url+'thread/'+o.id+'/'+o.id+'">回复</a></span><hr style="height:0px;border:0px;clear:both;"/></div>';
			} catch(e) {
				//just ignore exceptions since some crap func stay in the object.
			}
		}
		widget.setBody(html+jw_footer);
	});
};
widget.onRefresh = widget.onLoad;
]]></script>
</head>
<body>
<div><p class="alert">正在加载……</p></div>
</body>
</html>

