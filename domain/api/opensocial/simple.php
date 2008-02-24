<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<Module>
<ModulePrefs title="叽歪"
		title_url="http://jiwai.de"
		thumbnail="http://asset.jiwai.de/gadget/opensocial/simple_thumbnail.png"
		screenshot="http://asset.jiwai.de/gadget/opensocial/simple_screenshot.png"
		author="叽歪"
		author_link="http://jiwai.de/freewizard/"
		author_email="freewizard@jiwai.com"
		author_affiliation="JiWai.de"
		description="叽歪的窗可贴 for iGoogle and OpenSocial(Orkut, MySpace, Ning, Hi5...)">
	<Require feature="dynamic-height" />
	<Require feature="setprefs" />
</ModulePrefs>
<UserPref name="screenName" display_name="你的叽歪登录名: " required="true" />
<UserPref name="withFriends" display_name="显示好友的更新: " required="true" datatype="bool"/>
<UserPref name="numDisplay" display_name="最多显示: " required="true" datatype="enum" default_value="5">
	<EnumValue value="1" display_value="1条"/>
	<EnumValue value="3" display_value="3条"/>
	<EnumValue value="5" display_value="5条"/>
	<EnumValue value="10" display_value="10条"/>
	<EnumValue value="20" display_value="20条"/>
</UserPref>
<Content type="html">
<![CDATA[
<style>
span {font-size:12px;}
img {width:24px; height:24px;}
</style>
<div id="main_container">
<div id="content"><p class="alert"><b>Please wait...</b></p></div>
</div>
<script type="text/javascript">
var prefs = new _IG_Prefs(__MODULE_ID__);
var screenName = prefs.getString("screenName");
var withFriends = parseInt(prefs.getString("withFriends"));
var numDisplay = parseInt(prefs.getString("numDisplay"));
if (numDisplay<1) numDisplay = 1;
if (numDisplay>20) numDisplay = 20;
function onInit() {
	if (screenName == null || screenName == '') {
		_gel("content").innerHTML = '<p class="alert"><b>请先设置你的叽歪登录名</b><br />还没有叽歪帐号？<a href="http://jiwai.de/wo/account/create" target="_blank">这里注册</a></p>';
	} else {
		var uri = 'http://api.jiwai.de/statuses/'+(withFriends ? 'friends' : 'user')+'_timeline/' + screenName + '.json?count='+numDisplay;
		_IG_FetchContent(uri, onFetch, 10);
	}
}
function formatDate(s) {
	var d = new Date(s.replace(/(\w+)\s+(\w+)\s+(\w+)\s+([\w:]+)\s+([\w+-]+)\s+(\w+)/, '$1 $2 $3 $6 $4 $5'));
	return (d.toLocaleDateString() == (new Date).toLocaleDateString()) 
		? d.toLocaleTimeString().replace(/(\d+):(\d+):(\d+)/, '$1:$2') 
		: d.toLocaleDateString();
}
function onFetch(response) {
	try {
		response = eval('('+response+')');
	} catch(e) {
	}
	if (response == null) {
		_gel('content').innerHTML = "<i>Invalid data.</i>";
		return;
	}
	var html = '';//'<table id="contents_table">';
	var l = 0;
	for (var i in response) {
		var o = response[i];
		l++;
		//html += '<tr><td>'+o.text+'</td><td>'+o.created_at+'</td></tr>';
		try {
		html += '<div style="padding-bottom:6px;border-bottom:solid 1px #eee;">'
		+ '<span><img style="float:right" src="'+o.user.profile_image_url //align="right"
		+ (withFriends ? '"/></span><span>' + o.user.screen_name + ': ' : '"/>')
		+ '</span><span>' + o.text
		+ '</span> <span style="color:grey">' + formatDate(o.created_at)
		+ '</span></div>';
		} catch (e) {
		}
	}
	//html += '</table>';
	_gel('content').innerHTML = html;
	_IG_AdjustIFrameHeight();
}
_IG_RegisterOnloadHandler(onInit);
</script>
]]>		
</Content>
</Module>
