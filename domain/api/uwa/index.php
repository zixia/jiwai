<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:widget="http://www.netvibes.com/ns/">
<head><title>叽歪</title>
<link rel="icon" type="image/x-icon" href="http://asset.jiwai.de/img/favicon.ico" />
<meta name="author" content="叽歪" />
<meta name="website" content="http://jiwai.de" />
<meta name="description" content="叽歪窗可贴 for UWA (SOW and etc)" />
<meta name="version" content="1.0" />
<meta name="keyword" content="叽歪 JiWai widget" />
<meta name="screenshot" content="http://asset.jiwai.de/gadget/uwa/sow_screenshot.png" />
<meta name="thumbnail" content="http://asset.jiwai.de/gadget/uwa/sow_thumbnail.png" />
<meta name="debugMode" content="false" />
<meta name="autoRefresh" content="300" />
<widget:preferences>
	<preference type="text" label="你的叽歪登录名" name="screenName" />
	<preference type="boolean" label="显示好友的更新" name="withFriends" defaultValue="true" />
	<preference type="range" label="最多显示" name="numDisplay" min="1" max="20" step="1" defaultValue="5" />
</widget:preferences>
<script type="text/javascript">
<![CDATA[
function formatDate(d) {
	return (d.toLocaleDateString() == (new Date).toLocaleDateString()) ? d.toLocaleTimeString() : d.toLocaleDateString();
}
widget.onLoad = function() {
	var screenName = widget.getValue("screenName");
	var withFriends = widget.getValue("withFriends") == 'true';
	var numDisplay = widget.getValue("numDisplay");
	if (numDisplay<1) numDisplay = 1;
	if (numDisplay>20) numDisplay = 20;
	if (screenName == null || screenName == '') {
		widget.setBody('<p class="alert">请先设置你的叽歪登录名<br />还没有叽歪帐号？<a href="http://jiwai.de/wo/account/create" target="_blank">这里注册</a></p>');
		return;
	}
	var uri = 'http://api.jiwai.de/statuses/'+(withFriends ? 'friends' : 'user')+'_timeline/' + screenName + '.json?count='+numDisplay;
	UWA.Data.getJson(uri, function(response){
		if (response == null) {
			widget.setBody('<p class="alert">Invalid data.</p>');
			return null;
		}
		var html = '';
		var l = 0;
		for (var i in response) {
			var o = response[i];
			l++;
			try{
				html += '<div style="padding-bottom:4px;margin-top:4px;border-bottom:solid 1px #eee;">'
				+ '<span><img style="float:right;width:24px;height:24px;" src="'+o.user.profile_image_url //align="right"
				+ (withFriends ? '"/></span><span>' + o.user.screen_name + ': ' : '"/>')
				+ '</span><span>' + o.text
				+ '</span> <span style="color:grey">' + formatDate(new Date(o.created_at))
				+ '</span></div>';
			} catch(e) {
				//just ignore exceptions since some crap func stay in the object.
			}
		}
		widget.setBody(html);
	});
};
widget.onRefresh = widget.onLoad;
]]></script>
</head>
<body>
<div id="content"><p class="alert">Please wait...</p></div>
</body>
</html>

