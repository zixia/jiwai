<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$outInfo = $user_info;

if( $new_user_info )
{
	$notice_html = null;
	$error_html = null;

	$array_changed = array();

	if( $new_user_info['interest'] != @$outInfo['interest'] ) {
		$array_changed['interest'] = $new_user_info['interest'];
	}

	if( $new_user_info['bookWriter'] != @$outInfo['bookWriter'] ) {
		$array_changed['bookWriter'] = $new_user_info['bookWriter'];
	}

	if( $new_user_info['player'] != @$outInfo['player'] ) {
		$array_changed['player'] = $new_user_info['player'];
	}

	if( $new_user_info['music'] != @$outInfo['music'] ) {
		$array_changed['music'] = $new_user_info['music'];
	}

	if( $new_user_info['artist'] != @$outInfo['artist'] ) {
		$array_changed['artist'] = $new_user_info['artist'];
	}

	if( count( $array_changed ) ) {
		if( count( $array_changed ) ) {
			JWUser::Modify( $user_info['id'], $array_changed );
			JWSession::SetInfo('notice', '修改个人资料成功');
		}
		JWTemplate::RedirectToUrl();
	}
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
<?php
$photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings" class="now">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" method="post" name="f">
<p class="right14"><a href="/wo/account/settings">帐户信息</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/password">修改密码</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/photos">头像</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/profile">个人资料</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/interest" class="now">兴趣爱好</a></p>
    <p class="Interesting">填写这些内容，你将有更多机会认识和自己志同道合的朋友。填写多个的话，可以用逗号分开，例如：吃饭，睡觉，打豆豆</p>
	<div id="wtRegist" style="margin:30px 0 0 0; width:520px">
        <ul>
	    <li class="box5">兴趣爱好</li>
		<li class="box6"><textarea name="user[interest]" maxlength="200" rows="3" class="textarea position"><?php echo htmlSpecialChars($user_info['interest']);?></textarea></li>
		<li class="box10"></li>
		<li class="box5">喜欢的书和作者</li>
		<li class="box6"><textarea name="user[bookWriter]" maxlength="200" rows="3" class="textarea position"><?php echo htmlSpecialChars($user_info['bookWriter']);?></textarea></li>
		<li class="box10"></li>
		<li class="box5">喜欢的电影和演员</li>
		<li class="box6"><textarea name="user[player]" maxlength="200" rows="3" class="textarea position"><?php echo htmlSpecialChars($user_info['player']);?></textarea></li>
		<li class="box10"></li>
		<li class="box5">喜欢的音乐和歌手</li>
		<li class="box6"><textarea name="user[music]" maxlength="200" rows="3" class="textarea position"><?php echo htmlSpecialChars($user_info['music']);?></textarea></li>
		<li class="box10"></li>
		<li class="box5">喜欢的地方</li>
		<li class="box6"><textarea name="user[artist]" maxlength="200" rows="3" class="textarea position"><?php echo htmlSpecialChars($user_info['artist']);?></textarea></li>
		<li class="box10"></li>
		<li class="box7">
	   <input type="submit" id="save" class="submitbutton" value="保存" />
	   </li>
		</ul>
       </div><!-- wtregist -->
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtmainblock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php jwtemplate::footer() ?>
</body>
</html>
