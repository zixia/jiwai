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
<script type="text/javascript">
window.jiwai_init_hook_qqscript = function()
{
	JiWai.AddScript('/js/qqrsa.js');
}
</script>
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
	<p class="black15bold">１寻找Google Talk好友</p>
	<p><a href="/wo/invitations/invitegoogle">点击这里寻找你的Google Talk联系人</a></p>
</form>
</div>

<div class="lookfriend">
<form class="validator">
	<p class="black15bold">２寻找Windows Live Messenger好友</p>
	<p><a href="/wo/invitations/invitelive">点击这里寻找你的Windows Live Messenger联系人</a></p>
</form>
</div>

<div class="lookfriend">
<form class="validator">
	<p class="black15bold">３寻找邮箱联系人中的好友</p>
	<div class="box1">
		<p>邮箱帐户<input type="text" name="emailusername" id="emailusername" class="inputStyle" style="width:86px;"/>@<select name="emaildomain" id="emaildomain">
				<option value="">--选择服务商--</option>
				<option value="163.com">163.com</option>
				<option value="126.com">126.com</option>
				<option value="sina.com">sina.com</option>
				<option value="sohu.com">sohu.com</option>
				<!--option value="qq.com">qq.com</option-->
				<option value="yahoo.com.cn">yahoo.com.cn</option>
				<option value="yahoo.cn">yahoo.cn</option>
				<option value="live.com">live.com</option>
				<option value="live.cn">live.cn</option>
				<option value="hotmail.com">hotmail.com</option>
				<option value="msn.com">msn.com</option>
				<option value="gmail.com">gmail.com</option>
				</select><i></i></p>
		<p>邮箱密码<input id="emailpassword" name="emailpassword" type="password" class="inputStyle"/><i></i></p>
	</div>
	<p class="po"><input type="button" class="submitbutton" value="寻找好友" onclick="JWAction.importFriend('email');"/></p>
</form>
</div>

<div class="lookfriend">
<form method="post" action="/wo/invitations/do" enctype="multipart/form-data">
	<p class="black15bold">４寻找通讯录内好友</p>
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
