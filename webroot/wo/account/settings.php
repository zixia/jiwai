<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

JWLogin::MustLogined();


$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];

$is_reset_password = JWSession::GetInfo('reset_password', false);
$is_web_user = JWUser::IsWebUser($user_info['idUser']);

$outInfo = $user_info;

if ( isset($new_user_info) && $_POST['commit_u'] )
{
	$nameScreen	= trim(@$new_user_info['nameScreen']);
	$email		= trim(@$new_user_info['email']);

	// compatible the twitter url param: name & description
	if ( empty($nameScreen) )
		$nameScreen	= trim(@$new_user_info['name']);

	$arr_changed 	= array();
	$error_html 	= null;
	$notice_html	= null;

	if ( isset($nameScreen) && $nameScreen!=$user_info['nameScreen'] )
	{
		$arr_changed['nameScreen'] = $nameScreen;
		$outInfo['nameScreen'] = $nameScreen;

		if ( !JWUser::IsValidName($nameScreen) )
		{
			$error_html .= <<<_HTML_
<li>用户名 <strong>$nameScreen</strong> 由最短为5位的字母、数字、下划线和小数点组成，且不能以数字开头。</li>
_HTML_;
		}

		if ( JWUser::IsExistName($nameScreen) )
		{
			$error_html .= <<<_HTML_
<li>用户名 <strong>$nameScreen</strong> 已经被使用。</li>
_HTML_;
		}
	}
	
	if ( isset($email) && $email!=$user_info['email'] )
	{
		$arr_changed['email'] = $email;
	
		if ( !JWUser::IsValidEmail($email,true) )
		{
			$error_html .= <<<_HTML_
<li><strong>$email</strong> 不正确。请输入正确的、可以工作的Email地址</li>
_HTML_;
		}

		if ( JWUser::IsExistEmail($email) )
		{
			$error_html .= <<<_HTML_
<li>Email <strong>$email</strong> 已经被使用。</li>
_HTML_;
		}
	}

	$nameUrl = isset($_POST['nameUrl']) ? $_POST['nameUrl'] : null;
	$oldNameUrl = $user_info['nameUrl'];
	if( $nameUrl && ('N'==$user_info['isUrlFixed'] ))
	{
		$arr_changed['nameUrl'] = $nameUrl;
		$arr_changed['isUrlFixed'] = 'Y';
	}

	if ( empty($error_html) && false == empty($arr_changed) )
	{
		if ( ! JWUser::Modify($user_info['id'],$arr_changed) )
		{
			JWSession::SetInfo('error', '用户信息更新失败，请稍后再试。');
		}

		JWSession::SetInfo('notice', '用户信息修改成功！');

	}else{
		JWSession::SetInfo('error', $error_html);
	}

	JWTemplate::RedirectToUrl();
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

<?php
if ( empty($error_html) )
	$error_html = JWSession::GetInfo('error');
if ( empty($notice_html) )
	$notice_html = JWSession::GetInfo('notice');
echo $notice_html;
echo $error_html;
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
<form id="f" action="" method="post" name="f">
<input type="hidden" name="commit_u" value="1"/>
<p class="right14"><a href="/wo/account/settings" class="now">帐户信息</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/password">修改密码</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/photos">头像</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/profile">个人资料</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/interest">兴趣爱好</a></p>
	<div id="wtRegist" style="margin:30px 0 0 0;width:520px">
        <ul>
	    <li class="box5">用户名</li>
		<li class="box6">
		<input name="user[nameScreen]" type="text" id="user_nameScreen" value="<?php echo $outInfo['nameScreen'];?>" ajax="nameScreen" alt="用户名" class="inputStyle"/><i></i>
		<li class="box7">用来登陆叽歪de（4个字符以上）</li>
		<li class="box5">邮<span class="mar">箱</span></li>
		<li class="box6">
		<input id="user_email" name="user[email]" type="text" value="<?php echo $outInfo['email']; ?>" ajax="email" alt="Email" class="inputStyle"/><i></i>
		<li class="box7">用于找回密码和接收通知</li>
		</ul>
		<?php if( $user_info['isUrlFixed'] == 'Y'  ) { ?>
        <ul>
		<li class="box5">你的URL</li>
		<li class="box8"><a href="<?echo JW_SRVNAME .'/' .$user_info['nameUrl'] .'/';?>"><?echo JW_SRVNAME .'/' .$user_info['nameUrl'] ;?></a></li>
		</ul>
		<?php } else{ ?>
		<div class="account">
        <ul>
		<li class="box5">你的URL</li>
		<li class="box8"><a href="<?echo JW_SRVNAME .'/' .$user_info['nameUrl'] .'/';?>"><?echo JW_SRVNAME .'/' .$user_info['nameUrl'] ;?></a></li>
		<li class="box7">你可以设置个性URL地址，但是只能修改一次，以后不能修改！这样做的原因是避免别人链接到你的主页时产生坏的链接。如果现在你不确定你想要的名字，可以暂时维持现状，等以后再说。 </li>
		<li class="box5">永久地址</li>
		<li class="box8"><?echo JW_SRVNAME .'/';?><input id="nameUrl" name="nameUrl" type="text" value="" style="width:80px;" ajax="nameUrl" null="true" alt="主页地址" class="inputStyle"/><i></i></li>
		</ul>
		</div><!-- line -->
		<?php } ?>
      <div style="overflow: hidden; clear: both;line-height:30px;"></div>
		<ul>
		<li><div style="overflow: hidden; clear: both;line-height:30px;"></div></li>
		<li class="box7"><input type="submit" id="reg" class="submitbutton" value="保存" /></li>
	   </ul>
       </div><!-- wtRegist -->
      <div style="overflow: hidden; clear: both;line-height:30px;"></div>
</form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>
<script defer="true">
	JWValidator.init('f');
</script>
</body>
</html>
