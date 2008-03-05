<?php
require_once('../../../jiwai.inc.php');

JWLogin::MustLogined();

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = JWLogin::GetCurrentUserId();

$protected = $current_user_info['protected'] == 'Y';
$idInvited = JWUser::GetIdEncodedFromIdUser( $current_user_id );
$num_status = JWStatus::GetStatusNum($current_user_id);
$num_following = JWFollower::GetFollowingNum($current_user_id);
$num_follower = JWFollower::GetFollowerNum($current_user_id);

$photo_url = JWPicture::GetUserIconUrl($current_user_id, 'thumb96');

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
		<li><a id="tab_import" href="invite" class="">寻找好友</a></li>
		<li><a id="tab_email" href="email" class="now">Email邀请</a></li>
		<li><a id="tab_sms" href="sms" class="">短信邀请</a></li>
	</ul>
</div>
<!-- leftdiv -->

<!-- rightdiv -->
<div class="rightdiv">
<div id="invite_email"><div class="lookfriend">
<form method="post" action="do">

<div id="email_invite_form">
	<p class="box3">
		<span class="pad2">多个收件人用回车或者（,）分隔</span><span class="black15bold">好友的EMail</span>
	</p>
	<p><textarea name="email_addresses" rows="3" class="textarea"></textarea></p>
	<p>
		<span class="black15bold">主题</span> <span class="black14"><input style="width:440px;color:#666666;" type="text" name="subject" value="你的朋友&nbsp;<?php echo $current_user_info['nameScreen']; ?>(<?php echo $current_user_info['nameFull']; ?>)&nbsp;邀请你加入叽歪" /></span>
	</p>
</div>

<p class="black15bold">内容</p>
<div class="Emaildiv">
	<div class="Emailcontentbox">
		<div class="Emailhead"><a href="http://jiwai.de/<?php echo $current_user_info['nameUrl']; ?>/"><img width="96" height="96" title="<?php echo $current_user_info['nameFull']; ?>" src="<?php echo $photo_url; ?>"/></a></div>
		<div class="Emailcont">
			<p class="black15bold">你的朋友&nbsp;<?php echo $current_user_info['nameScreen']; ?>(<?php echo $current_user_info['nameFull']; ?>)&nbsp;邀请你加入叽歪</p>
			<p class="meta">叽歪网能让你用一句话建立自己的博客，用只言片语记录生活轨迹</p>
			<!-- meta -->
		</div><!-- Emailcont -->
	</div><!-- Emailcontentbox -->

	<div class="Emailcontentbox">
		<div class="boxright">
		<ul>
			<li>请点击这里<strong>接受邀请</strong>，注册后直接开始关注&nbsp;<?php echo $current_user_info['nameScreen']; ?>&nbsp;</li>
			<li><a href="http://jiwai.de/wo/invitations/i/<?php echo $idInvited; ?>">http://jiwai.de/wo/invitations/i/<?php echo $idInvited; ?></a></li>
			<li>你也可以在这里关注&nbsp;<?php echo $current_user_info['nameScreen']; ?>(<?php echo $current_user_info['nameFull']; ?>)&nbsp;的<strong>最新动态</strong></li>
			<li><a href="http://jiwai.de/<?php echo $current_user_info['nameUrl']; ?>/" rel="contact">http://jiwai.de/<?php echo $current_user_info['nameUrl']; ?>/</a></li>
		</ul>
		</div>
		<!-- boxright -->

		<div class="boxleft">
		<ul>
			<li class="Emailorange14"><?php echo $current_user_info['nameScreen']; ?></li>
			<li><?php echo $num_status; ?>&nbsp;条叽歪</li><li>关注&nbsp;<?php echo $num_following; ?>&nbsp;人</li><li>被&nbsp;<?php echo $num_follower; ?>&nbsp;人关注</li></ul>
		</div>
		<!-- boxleft -->
	</div><!-- Emailcontentbox -->
</div>
<!-- Emaildiv -->

<p><center><input name="invite_email_x" type="submit" class="submitbutton" value="发送邀请" /></center></p>
</form>

</div></div>
<!-- lookfriend -->
</div>
<!-- rightdiv end -->
</div>
<!-- #wtMainBlock -->

<?php JWTemplate::container_ending(); ?>
</div><!-- #container -->

<?php JWTemplate::footer(); ?>

</body>
</html>
