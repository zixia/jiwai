<?php
require_once('../../../jiwai.inc.php');

JWLogin::MustLogined();

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = JWLogin::GetCurrentUserId();

/* owner's binded device */
$device_rows = JWDevice::GetDeviceRowByUserId( $current_user_id );
$msn_address = isset( $device_rows['msn'] ) ? $device_rows['msn']['address'] : null;
$gtalk_address = isset( $device_rows['gtalk'] ) ? $device_rows['gtalk']['address'] : null;

?>
<?php JWTemplate::html_doctype(); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)) 
?>
</head>

<body class="account" id="friends">

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">寻找与邀请好友</p>
<div id="wtMainBlock">

<!-- leftdiv begin -->
<div class="leftdiv">
	<ul class="leftmenu">
		<li><a id="tab_import" href="invite" class="now">寻找好友</a></li>
		<li><a id="tab_email" href="email" class="">Email邀请</a></li>
		<li><a id="tab_sms" href="sms" class="">短信邀请</a></li>
	</ul>
</div>
<!-- leftdiv end -->

<!-- rightdiv begin -->
<div class="rightdiv"><div id="invite_import" style="display:block;">

<div class="lookfriend">
<form class="validator">
	<p class="black15bold">寻找GTalk好友</p>
	<p>输入你的GTalk帐号和密码，寻找你的GTalk好友，很可能他们也在叽歪</p>
	<div class="box1">
		<p>你的GTalk号码<input id="gtalkusername" name="gtalkusername" type="text" value="<?php echo $gtalk_address;?>" class="inputStyle" /><i></i></p>
		<p>GTalk密码<input id="gtalkpassword" name="gtalkpassword" type="password" class="inputStyle" /><i></i></p>
	</div>
	<p class="po"><input type="button" class="submitbutton" value="寻找好友" onclick="JWAction.importFriend('gtalk');"/></p>
</form>
</div>

<div class="lookfriend">
<form class="validator">
	<p class="black15bold">寻找MSN好友</p>
	<p>输入你的MSN帐号和密码，寻找你的MSN好友，很可能他们也在叽歪</p>
	<div class="box1">
		<p>你的MSN号码<input id="msnusername" name="msnusername" type="text" value="<?php echo $msn_address;?>" class="inputStyle" /></p>
		<p>MSN密码<input id="msnpassword" name="msnpassword" type="password" class="inputStyle" /></p>
	</div>
	<p class="po"><input type="button" class="submitbutton" value="寻找好友" onclick="JWAction.importFriend('msn');" /></p>
</form>
</div>

<div class="lookfriend">
<form method="post" action="/wo/invitations/do" enctype="multipart/form-data">
	<p class="black15bold">寻找通讯录内好友</p>
	<p>上传你的通讯录文件，寻找你通讯录内的好友，很可能他们也在叽歪</p>
	<p class="file"><input name="friends_lists" type="file" class="inputStyle2" /></p>
	<p class="po"><input name="uploadfile" type="submit" class="submitbutton" value="上传文件" /></p>
	<p class="gray12">你可以上传 txt , csv 文件。<a target="_blank" href="http://help.jiwai.de/HowToExportCSV" class="orange12">如何从outlook导出csv文件</a></p>
</form>
</div>

</div></div>
<!-- rightdiv end -->

</div>
<!-- wtMainBlock end -->

<?php JWTemplate::container_ending(); ?>
</div>
<!-- container end -->

<?php JWTemplate::footer(); ?>

</body>
</html>
