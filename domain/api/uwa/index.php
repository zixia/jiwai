<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:widget="http://www.netvibes.com/ns/">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>叽歪</title>
<link rel="icon" type="image/x-icon" href="http://asset.jiwai.de/img/favicon.ico" />
<meta name="author" content="叽歪" />
<meta name="website" content="http://jiwai.de" />
<meta name="description" content="在个人空间和博客上显示你的叽歪窗可贴，支持彩信图片显示和回复等功能。" />
<meta name="version" content="1.1.1" />
<meta name="keyword" content="叽歪 JiWai 彩信 微博客 miniblog twitter 推客 饭否 fanfou" />
<meta name="screenshot" content="http://asset.jiwai.de/gadget/uwa/sow_screenshot.png" />
<meta name="thumbnail" content="http://asset.jiwai.de/gadget/uwa/sow_thumbnail.png" />
<meta name="debugMode" content="false" />
<meta name="autoRefresh" content="300" />
<widget:preferences>
	<preference type="text" label="你的叽歪登录名" name="screenName" />
	<preference type="boolean" label="显示好友的更新" name="withFriends" defaultValue="true" />
	<preference type="range" label="最多显示" name="numDisplay" min="1" max="20" step="1" defaultValue="5" />
	<preference type="boolean" label="显示留言框" name="updateBox" defaultValue="true" />
</widget:preferences>
<script type="text/javascript">
<![CDATA[
function formatStatus(s) {
	return s.replace(/\[(.+)\]/, '[<a target="_blank" href="http://jiwai.de/t/$1/">$1</a>]');
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
	var withFriends = widget.getValue("withFriends") == 'true';
	var numDisplay = widget.getValue("numDisplay");
	var updateBox = widget.getValue("updateBox") == 'true';
	var uri;
	if (numDisplay<1) numDisplay = 1;
	if (numDisplay>20) numDisplay = 20;
	if (screenName == null || screenName == '') {
		jw_footer = '<p class="alert">现在显示的是叽歪广场。设置你的叽歪帐号后就可以在这里显示你和好友的最新消息了。还没有叽歪帐号？<a href="http://jiwai.de/wo/account/create" target="_blank">这里注册</a></p>'
		widget.setBody(jw_footer);
		uri = 'http://api.jiwai.de/statuses/public_timeline.json?count=5';
		withFriends = true;
	} else {
		widget.setTitle(screenName+(withFriends ? '和朋友们' : '')+'的叽歪');
		uri = 'http://api.jiwai.de/statuses/'+(withFriends ? 'friends' : 'user')+'_timeline/' + encodeURIComponent(screenName) + '.json?count='+numDisplay;
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

