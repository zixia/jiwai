<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$service = 'twitter';
$service_name = 'Twitter';

$user_info = JWUser::GetCurrentUserInfo();

$bind_id = $bindother_id = $login_name = $login_pass = null;
$sync_reply = $sync_conference = 'N';

extract($_POST, EXTR_IF_EXISTS);

if ( $login_name && $login_pass )
{
	$options = array(
		'sync_reply' => $sync_reply,
		'sync_conference' => $sync_conference,
	);
	if (JWBindOther::Create($user_info['id'], $login_name, $login_pass, $service, $options ))
	{
		$notice_html = '绑定 '.$service_name.' 成功。';
		JWSession::SetInfo('notice', $notice_html);
	}
	else
	{
		$error_html = $service_name.' 用户名 或 密码 错误。';
		JWSession::SetInfo('error', $error_html);
	}
	JWTemplate::RedirectToUrl();
}
else if ( !empty($bind_id) )
{
    $options = array(
        'syncReply' => $sync_reply,
        'syncConference' => $sync_conference,
    );
    $is_succ = JWDB::UpdateTableRow( 'BindOther', $bind_id, $options );
    if($is_succ)
        JWSession::SetInfo('notice', '修改同步选项成功！');
    else
        JWSession::SetInfo('notice', '修改同步选项失败！');
    JWTemplate::RedirectToUrl();
}

if ( $bindother_id )
{
	JWBindOther::Destroy($user_info['id'], $bindother_id);
	$notice_html = '解除绑定 '.$service_name.' 成功。';
	JWSession::SetInfo('notice', $notice_html);

	JWTemplate::RedirectToUrl();
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
<p class="right14"><a href="/wo/devices/sms">手机</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/devices/im">聊天软件</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/bindother/" class="now">其他网站</a></p>
       <div class="binding">
<?php
	$bind = JWBindOther::GetBindOther($user_info['id']);
	$bind = isset($bind[$service]) ? $bind[$service] : array();
	$bind_login_name = null;

	if ( false == empty($bind) ) 
	{
		$sync_reply = $bind['syncReply'];
		$sync_conference = $bind['syncConference'];
		$bind_login_name = $bind['loginName'];

		echo <<<_HTML_
			<form id="f1" method="post">
				<p><span class="black15bold">已绑定你的${service_name}帐号${bind_login_name}&nbsp;&nbsp;</span>(<a href="javascript:void(0);" onclick="if(confirm('你真的要删除 $service_name 绑定吗？'))$('f1').submit();return false;">删除并重设</a>)</p>
				<input type="hidden" name="bindother_id" value="$bind[id]"/>
			</form>
_HTML_;
	}
	else
echo <<<_HTML_
	   <p class="black15bold">绑定$service_name</p>
_HTML_;
echo <<<_HTML_
	   <p class="bindingOtheredit">将你的叽歪自动同步到${service_name}，而不是从${service_name}更新到叽歪</p>
_HTML_;
?>
<form id="f" action="" method="post" name="f" class="validator">
    <? if ( empty($bind) ) {?>
<p class="twitter"><label for="login_name">帐号<input name="login_name" type="text" id="login_name" value="<?php echo $bind_login_name;?>" alt="帐号" title="帐号" check="null" class="inputStyle"/><i></i></label></p>
<p class="twitter"><label for="login_pass">密码<input id="login_pass" name="login_pass" type="password" value="" alt="密码" title="密码" check="null" class="inputStyle"/><i></i></label></p>
<? } ?>
				<p class="twitterCheckbox"><label for="sync_reply"><input id="sync_reply" name="sync_reply" type="checkbox" value="Y" <?php echo $sync_reply=='Y'?'checked' : '';?>/>同步回复到<?php echo $service_name;?></label></p>
				<p class="twitterCheckbox"><label for="sync_conference"><input id="sync_conference" name="sync_conference" type="checkbox" value="Y" <?php echo $sync_conference=='Y'?'checked':'';?>/>同步会议发言到<?php echo $service_name;?></label></p>
    <? if ( false == empty($bind) )
    echo <<<_HTML_
                <input type="hidden" name="bind_id" value="$bind[id]"/>
_HTML_;
?>
		<p class="twitterButt"><input type="submit" class="submitbutton" value="保存"/></p>
	   </div><!-- binding -->
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
