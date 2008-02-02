<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$outInfo = $user_info;
$photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
if ( !empty($_POST) )
{
	$new_user_info['protected'] = isset($new_user_info['protected'])?'Y':'N';
	$new_user_info['messageFriendOnly'] = isset($new_user_info['messageFriendOnly'])?'Y':'N';
	if( $new_user_info['protected'] != $outInfo['protected'] ) {
		$array_changed['protected'] = $new_user_info['protected'];
	}
	if( $new_user_info['messageFriendOnly'] != $outInfo['messageFriendOnly'] ) {
		$array_changed['messageFriendOnly'] = $new_user_info['messageFriendOnly'];
	}

	if( 0<count( $array_changed )) {
		JWUser::Modify( $user_info['id'], $array_changed );
		JWSession::SetInfo('notice', '修改个人资料成功');
	}
	JWTemplate::RedirectBackToLastUrl("/");
}

?>
<html>
<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy/" class="now">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" action="" method="post" name="f">
       <div class="protection">
	   <p><label for="messageFriendOnly"><input type="checkbox" id="messageFriendOnly" name="user[messageFriendOnly]" value="Y" <?php if($outInfo['messageFriendOnly']=='Y') echo 'checked';?>/>&nbsp;只允许我关注的人给我发送悄悄话</label></p>
	   <p><label for="protect"><input type="checkbox" id="protect" name="user[protected]" value="Y" <?php if($outInfo['protected']=='Y') echo 'checked="true"';?>/>&nbsp;只对我关注的人开放我的叽歪</label></p>
	   <p class="checkboxText">其他任何人将无法看到你的叽歪，也不会被搜索引擎获取</p>	   
	   <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
	   <p><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></p>
	   </div>
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
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
