<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info		= JWUser::GetCurrentUserInfo();

$openid_id		= JWOpenID::GetIdByUserId($user_info['idUser']);

if ( isset($_REQUEST['save']) )
{
	// 用户输入了自己的  openid，需要去验证
	$openid_url = $_REQUEST['user']['openid'];

	if ( JWOpenID::IsPossibleOpenID($openid_url) )
	{
		JWOpenID::AuthRedirect($openid_url);
		// if it return, mean $username_or_email is not a valid openid url.
	}
	else
	{
		$error_html = <<<_HTML_
你输入的 OpenID：$openid_url 有误，请查证后重试。
_HTML_;
		JWSession::SetInfo('notice', $error_html);
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
<?php JWTemplate::ShowActionResultTipsMain(); ?>
<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/" class="now">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" action="" method="post" name="f">
   <div class="protection">
	<div id="wtRegist" style="margin:30px 0 0 0; width:520px">
		<ul>

<?php
if ( true )
{
	// 用户自己的 openid
	$openid_db_row 	= JWOpenID::GetDbRowById($openid_id);
	if(empty($openid_db_row))
	{
		$openid_url = JW_SRVNAME . "/${user_info['nameUrl']}/";
	}
	else
	{
		$openid_url 	= JWOpenID::GetFullUrl($openid_db_row['urlOpenid']);
	}
	echo <<<_USER_OPENID_
		<li class="openIDbox">你现在的OpenID为：</li>
		<li class="openIDbox1">&nbsp;$openid_url</li>
		<li class="box9"></li>
		<li class="openIDbox"><label for="user_openid">设置你自己的OpenID：</label></li>
		<li class="openIDbox1"><input id="user_openid" name="user[openid]" size="30" type="text" class="inputStyle"/></li>
		<li class="box9"></li>
		<li class="openIDbox2"><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></li>
		<li class="box9"></li>
		</ul>
_USER_OPENID_;
}

$trusted_site_ids 		= JWOpenID_TrustSite::GetIdsByUserId($user_info['id']);
$trusted_site_db_rows 	= JWOpenID_TrustSite::GetDbRowsByIds($trusted_site_ids);

if ( true)
{
	echo <<<_HTML_
<p class="accountLine black15bold">当前允许的网站</p>
_HTML_;
foreach ( $trusted_site_ids as $trusted_site_id )
{
	$db_row = $trusted_site_db_rows[$trusted_site_id];
	echo <<<_HTML_
<p><a href="/wo/openid/trustsite/destroy/$db_row[id]">删除</a> <a href="$db_row[urlTrusted]" target="_blank"><strong>$db_row[urlTrusted]</strong></a></p>
_HTML_;
}
}
?>
			<p class="orange12"><a href="http://baike.baidu.com/view/832917.html" target="_blank">什么是 OpenID？</a>
			<!--a href="http://openids.cn/how-to-use-openid/" target="_blank">OpenID如何使用？</a --></p>
		</div><!-- wtRegist -->
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
