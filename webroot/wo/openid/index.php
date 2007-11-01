<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info		= JWUser::GetCurrentUserInfo();
$nameScreen=$_POST['user']['nameScreen'];
$password=$_POST['user']['password'];

if (isset($_POST['user']) && trim ($nameScreen) && trim ($password))
{
    if (JWBindOther::Create($user_info['id'], $nameScreen, $password))
    {
        $notice_html = <<<_HTML_
        绑定 Twitter 成功。
_HTML_;
        JWSession::SetInfo('notice', $notice_html);
    }
    else
    {
        $error_html = <<<_HTML_
        Twitter 用户名 或 密码 错误。
_HTML_;
        JWSession::SetInfo('error', $error_html);
    }

	header ( "Location: /wo/bindother/" );
    exit();

}

if (isset($_POST['idDelete']) && ($idDelete=intval($_POST['idDelete'])))
{
    JWBindOther::Destroy($user_info['id'], $idDelete);
    $notice_html = <<<_HTML_
    解除绑定 Twitter 成功。
_HTML_;
    JWSession::SetInfo('notice', $notice_html);

	header ( "Location: /wo/bindother/" );
    exit();
}
?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTips(); ?>
<?php
if ( empty($error_html) )
	$error_html = JWSession::GetInfo('error');
if ( empty($notice_html) )
	$notice_html = JWSession::GetInfo('notice');

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice"><ul> $error_html </ul></div>
_HTML_;
}


if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}

?>
<div id="container" class="subpage">
<?php JWTemplate::SettingTab(); ?>

<div class="tabbody">
<h2>绑定 Twitter</h2>

	<form id="f" method="post">
<?php
    $bind = JWBindOther::GetBindOther($user_info['id']);
    $bind = $bind['twitter'];

    if (!empty($bind))
	echo <<<_HTML_
    <div style="margin-left:20px; font-size:14px;font-weight:bold;">你已经成功绑定了 Twitter (<a href="javascript:void(0);" onclick="if(confirm('你确定要删除 Twitter 绑定吗？'))$('f').submit();return false;">删除</a>)</div>
	<input type="hidden" name="idDelete" value="$bind[id]"/>
_HTML_;
?>
	<fieldset>
	<table width="100%" cellspacing="3">
		<tr>
			<th valign="top">用户名：</th>
			<td width="260">
				<input name="user[nameScreen]" type="text" id="user_nameScreen" value="<?php echo $bind['loginName'];?>" alt="用户名" title="用户名" check="null"/><i></i>
			</td>
			<td class="note">用来登陆 Twitter 的用户名</td>
		</tr>
		<tr>
			<th>密码：</th>
			<td><input id="user[password]" name="user[password]" type="password" value="" alt="密码" title="密码" check="null"/><i></i></td>
			<td class="note">用来登陆 Twitter 的密码</td>
		</tr>
	</table>
	</fieldset>

	<div style=" padding:20px 0 0 160px; height:50px;">
		<a onclick="if(JWValidator.validate('f'))$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
	</div>

	</form>


  <!-- end of tricky part -->
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
