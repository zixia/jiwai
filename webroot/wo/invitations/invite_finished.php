<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);
JWTemplate::html_doctype();

$memcache = JWMemcache::Instance();
$mc_key = $_SESSION['Buddy_Import_Key'] ;
$memcache->Del( $mc_key );

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>

<body class="account" id="friends">

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">寻找与邀请好友</p>

<div id="wtMainBlock">

<!-- leftdiv start -->
<div class="leftdiv">
<ul class="leftmenu">
		<li><a id="tab_import" href="invite" class="now">寻找好友</a></li>
		<li><a id="tab_email" href="email" class="">Email邀请</a></li>
		<li><a id="tab_sms" href="sms" class="">短信邀请</a></li>
</ul>
</div>
<!-- leftdiv end -->

<!-- rightdiv start -->
<div class="rightdiv">
<div id="invite_import" style="display:block;">
<div class="lookfriend">
	<p class="black15bold">邀请朋友操作成功</p>
	<p><a href="<?php echo JW_SRVNAME ."/wo/invitations/invite";?>" class="orange12">试试用其他帐号邀请好友（MSN，GTALK等）</a></p>
</div><!-- lookfriend -->
<div style="clear:both;"></div>
</div>
</div>
<!-- rightdiv end-->

</div><!-- #wtMainBlock-->

<?php JWTemplate::container_ending();?>
</div><!-- #container -->

<?php JWTemplate::footer() ?>
</body>
</html>
