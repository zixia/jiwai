<?php
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined(false);

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];

?>

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
<li><a id="tab_import" href="invite" class="">寻找好友</a></li>
<li><a id="tab_email" href="email" class="">Email邀请</a></li>
<li><a id="tab_sms" href="sms" class="now">短信邀请</a></li>
</ul>
</div>
<!-- leftdiv end -->

<!-- rightdiv begin -->
<div class="rightdiv">

<div id="invite_sms" style="display:block">
<div class="lookfriend">
<form method="post" action="/wo/invitations/do">
	<p class="box4"><span class="pad2">每空只能填写一个手机号码</span><span class="black15bold">好友的手机号</span></p>
	<p><input name="sms_addresses[]" type="text" class="inputStyle1" />&nbsp;&nbsp;<input name="sms_addresses[]" type="text" class="inputStyle1" /></p>
	<p><input name="sms_addresses[]" type="text" class="inputStyle1" />&nbsp;&nbsp;<input name="sms_addresses[]" type="text" class="inputStyle1" /></p>
	<p><input name="sms_addresses[]" type="text" class="inputStyle1" />&nbsp;&nbsp;<input name="sms_addresses[]" type="text" class="inputStyle1" /></p>
	<p class="black15bold">短信内容</p>
	<p class="lineheight black14">我是<input name="sms_nickname" type="text" class="inputStyle1" style="width:180px;" value="<?php echo "$current_user_info[nameScreen]($current_user_info[nameFull])"; ?>"/>，我在叽歪网建立了我的碎碎念平台，你可以回复任何想说的话，开始你的碎碎念，回复 F 关注我（可以随时停止关注）</p>
	<p><center><input name="invite_sms_x" type="submit" class="submitbutton" value="发送邀请" /></center></p>
</form>
</div>
</div>

</div>
<!-- rightdiv -->

</div>
<!-- #wtMainBlock -->

<?php JWTemplate::container_ending(); ?>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
