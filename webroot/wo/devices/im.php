<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$current_user_info		= JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];
$device_rows 	= JWDevice::GetDeviceRowByUserId( $current_user_id );
$notice = JWSession::GetInfo('notice'); 

/*$supported_devices = JWDevice::GetSupportedDeviceTypes();
$supported_devices = array_diff( $supported_devices, array( 'sms','facebook','newsmth' ) );*/
$supported_devices = array('qq', 'msn', 'gtalk', 'fetion', 'skype', 'yahoo', 'aol', 'newsmth');

//echo "<pre>";(var_dump($user_setting));
if ( isset($_REQUEST['_shortcut']) )
{
    list($type, $address) = explode($_REQUEST['_shortcut'], ':');
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>

<body class="account">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms" class="now">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<p class="right14"><a href="/wo/devices/sms">手机</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/devices/im" class="now">聊天软件</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/bindother/">其他网站</a></p>
       <div class="binding">
	   <p>通过QQ、MSN、GTalk、飞信等聊天软件就可以接收和更新你的叽歪，现在就来绑定吧。</p>
<form id="f" action="/wo/devices/create" method="post" class="validator">
	   <p>输入帐号<input type="text" name="device[address]" check="null" alt="帐号" value="" class="inputStyle"/>&nbsp;<select name="device[type]" size="1" class="select Width80">
		  <option selected="selected" alt="帐号类型" value="">请选择</option>
<? foreach($supported_devices as $type){
if(isset($device_rows[$type])) continue;
$typename = JWDevice::GetNameFromType($type);
echo  "<option value=\"$type\">$typename</option>";
}
?>
		</select>&nbsp;&nbsp;<input type="submit" class="submitbutton" value="绑定" />
  </form>

<?php
		if(!empty($notice)) echo $notice;
		//$aDeviceInfo_rows = JWDevice::GetDeviceRowByUserId($current_user_id);
		foreach( $supported_devices as $type ) {
		//foreach( $aDeviceInfo_rows as $aDeviceInfo_row ) {
			if( isset($device_rows[$type]) && !empty($device_rows[$type]['secret'])) 
			{
				$device_row = $device_rows[$type];
				$address = $device_row['address'];
				$secret = $device_row['secret'];
				$device_id = $device_row['id'];
				$typename = JWDevice::GetNameFromType($type);
				$robot = JWDevice::GetRobotFromType($type , $address);
				$image = JWTemplate::GetAssetUrl("/images/jiwai-$type.gif");
				echo <<<_NOTICE_
       <div class="bindingIMbox">   
	   <p>你想要绑定的<img src="$image" /><b class="black14">${typename}帐号为&nbsp;$address</b>（<a style="font-size:14px;" href="/wo/devices/destroy/$device_id" onClick="if (true || confirm('你真的要删除 $typename 绑定吗？')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute( 'value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除</a>），没错吧？那就请按以下步骤操作：</p>
	   <p class="bindingblack">1. 请在${typename}上添加【${typename}叽歪小弟：${robot}】为好友</p>
	   <p class="bindingblack">2. 请复制以下验证信息发送给${typename}叽歪小弟，完成绑定</p>
       <p class="bindingblack">&nbsp;&nbsp;&nbsp;&nbsp;验证码：<input id="secret_${type}" type="text" value="$secret" readonly class="inputStyle3" onclick="JiWai.copyToClipboard(this);"/><span class="copytips" id="secret_${type}_tip">验证码复制成功</span></p>
	   </div>
_NOTICE_;
			}
		}
		echo '<div class="bindingIM"><span class="floatright personalizedText">发送更新消息给已绑定的<span class="black12">叽歪小弟</span>即可更新叽歪</span><span class="black14">已绑定的聊天软件</span></div>';
		foreach( $device_rows as $device_row ) 
		{
			if(empty($device_row['secret']))
			{
				$type = $device_row['type'];
				if(!in_array($type, $supported_devices))continue;
				$typename = JWDevice::GetNameFromType($type);
				$address = $device_row['address'];
				$checked = $device_row['isSignatureRecord'] == 'Y'? 'checked' : null;
				$image = JWTemplate::GetAssetUrl("/images/jiwai-$type.gif");
				echo <<<_HTML_
	    <div class="entry">
	    <div class="floatleft1"><img src="$image"/></div>
	    <div class="content">
		<div class="meta">
_HTML_
?>
		 <div style="cursor:pointer;" onclick="var ds=$('device_<? echo $device_row['id'];?>');var es=$('edit_<? echo $device_row['id'];?>');ds.style.display='none'==ds.style.display?'block':'none';es.innerHTML='none'==ds.style.display?'编辑':'隐藏';return false;"><span class="floatright orange12" id="edit_<? echo $device_row['id'];?>">编辑</span><span class="smallblack"><? echo $address;?></div>
		</span><div style="display:none;" id="device_<? echo $device_row['id'];?>">
        <p class="bindingIMedit smallblack12"><input style="width:14px; display:inline; border:none;" type="checkbox" value="<? echo $device_row['isSignatureRecord'];?>" id="notify_<?php echo $type;?>_sig" <?php if($device_row['isSignatureRecord']=='Y') echo "checked"; ?> onClick="this.value=(this.value=='Y' ? 'N' : 'Y'); JiWai.EnableDevice(<?php echo $device_row['id'];?>, 'isSignatureRecord='+this.value);"/> <label for="notify_<?php echo $type;?>_sig">将我的签名更新发布到叽歪</label><span class="copytips" style="display:inline;" id="tips_<? echo $device_row['id'];?>"></span></p>
		<p class="bindingIMeditCancel"><a class="orange12" href="/wo/devices/destroy/<?php echo $device_row['id']; ?>" onClick="if (confirm('你真的要删除 <?echo $typename;?> 绑定吗？')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除并重设</a></p></div>
<?
				echo <<<_HTML_
		</div>
		<!-- meta -->
		</div><!-- content -->
	    </div><!-- entry -->
_HTML_;
		}
}
?>
		</div>
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
