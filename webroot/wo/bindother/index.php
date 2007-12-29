<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$service = 'twitter';
$service_name = 'Twitter';

$user_info = JWUser::GetCurrentUserInfo();

$bindother_id = $login_name = $login_pass = null;
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

	JWTemplate::RedirectToUrl( '/wo/bindother/' );
}

if ( $bindother_id )
{
	JWBindOther::Destroy($user_info['id'], $bindother_id);
	$notice_html = '解除绑定 '.$service_name.' 成功。';
	JWSession::SetInfo('notice', $notice_html);

	JWTemplate::RedirectToUrl( '/wo/bindother/' );
}

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab(); ?>

<div class="tabbody">
<h2>绑定<?php echo $service_name;?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<span style="font-size:14px;">将你的叽歪自动同步到<?php echo $service_name;?>，而不是从<?php echo $service_name;?>更新到叽歪</span></h2>

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
				<div style="margin-left:20px; font-size:14px;font-weight:bold;">你已经成功绑定了 <?php echo $service_name;?> (<a href="javascript:void(0);" onclick="if(confirm('你确定要删除 $service_name 绑定吗？'))$('f1').submit();return false;">删除</a>)</div>
				<input type="hidden" name="bindother_id" value="$bind[id]"/>
			</form>
_HTML_;
	}
?>
	<form id="f" method="post">
	<fieldset>
	<table width="100%" cellspacing="3">
		<tr>
			<th valign="top">用户名：</th>
			<td width="260">
				<input name="login_name" type="text" id="login_name" value="<?php echo $bind_login_name;?>" alt="用户名" title="用户名" check="null"/><i></i>
			</td>
			<td class="note">用来登陆 <?php echo $service_name;?> 的用户名</td>
		</tr>
		<tr>
			<th>密码：</th>
			<td><input id="login_pass" name="login_pass" type="password" value="" alt="密码" title="密码" check="null"/><i></i></td>
			<td class="note">用来登陆 <?php echo $service_name;?> 的密码</td>
		</tr>
		<tr><td height="10" colspan="3"></td></tr>
		<tr>
			<th valign="top">选项：</th>
			<td>
				<input style="display:inline; width:16px; border:0px; height:16px;margin-right:10px;" id="sync_reply" name="sync_reply" type="checkbox" value="Y" <?php echo $sync_reply=='Y'?'checked' : '';?>/>同步回复到<?php echo $service_name;?><br/>
				<input style="display:inline; width:16px; border:0px; height:16px;margin-right:10px;" id="sync_conference" name="sync_conference" type="checkbox" value="Y" <?php echo $sync_conference=='Y'?'checked':'';?>/>同步会议发言到<?php echo $service_name;?>
			</td>
			<td class="note"></td>
	</table>
	</fieldset>

	<div style=" padding:20px 0 0 160px; height:50px;">
		<input onclick="if(JWValidator.validate('f'))$('f').submit();return false;" type="button" class="submitbutton" value="保存"/>
	</div>

	</form>


  <!-- end of tricky part -->
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
