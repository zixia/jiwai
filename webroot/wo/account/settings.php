<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();
$new_user_info	= @$_REQUEST['user'];

$is_reset_password	= JWSession::GetInfo('reset_password', false);
$is_web_user = JWUser::IsWebUser($user_info['idUser']);

$outInfo = $user_info;

//var_dump($user_info);

if ( isset($new_user_info) && $_REQUEST['commit_u'] )
{
	$nameFull	= trim(@$new_user_info['nameFull']);
    $nameScreen	= trim(@$new_user_info['nameScreen']);
    $email		= trim(@$new_user_info['email']);

	// compatible the twitter url param: name & description
	if ( empty($nameScreen) )
		$nameScreen	= trim(@$new_user_info['name']);

	$arr_changed 	= array();
	$error_html 	= null;
    $notice_html    = null;

	if ( isset($nameFull) && $nameFull!=$user_info['nameFull'] )
	{
		$arr_changed['nameFull'] = $nameFull;
        $outInfo['nameFull'] = $nameFull;
	}

	if ( isset($nameScreen) && $nameScreen!=$user_info['nameScreen'] )
	{
		$arr_changed['nameScreen'] = $nameScreen;
        $outInfo['nameScreen'] = $nameScreen;

		if ( 4>strlen($nameScreen) || !JWUser::IsValidName($nameScreen) )
		{
			$error_html .= <<<_HTML_
<li>帐号 <strong>$nameScreen</strong> 需由最短为5位的字母、数字、下划线和小数点组成，且不能短于6个字符。</li>
_HTML_;
		}

		if ( JWUser::IsExistName($nameScreen) )
		{
			$error_html .= <<<_HTML_
<li>帐号 <strong>$nameScreen</strong> 已经被使用。</li>
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

	if ( empty($error_html) 
			&& (!empty($arr_changed) ) )
	{
		if ( ! JWUser::Modify($user_info['id'],$arr_changed) )
		{
			$error_html = <<<_HTML_
<li>用户信息更新失败，请稍后再试。</li>
_HTML_;
			JWSession::SetInfo('error', $error_html);
		}

		$notice_html = <<<_HTML_
<li>用户信息修改成功！</li>
_HTML_;
		JWSession::SetInfo('notice', $notice_html);

		header ( "Location: /wo/account/settings" );
	}
}

/** check if reset_password */

    if ( $is_web_user && !$is_reset_password )
    {
        $verify_corrent_password = true;
    }
    else
    {
        $verify_corrent_password = false;
    }

if ( isset($_REQUEST['commit_p']) ) {
    if ( isset($_REQUEST['password']) )
    {
        $current_password 		= trim( @$_REQUEST['current_password'] );
        $password 				= trim( @$_REQUEST['password'] );
        $password_confirmation 	= trim( @$_REQUEST['password_confirmation'] );

        if ( $verify_corrent_password
                && (	empty($current_password) 
                        || empty($password)
                        || empty($password_confirmation) 
                ) )
        {
            $error_html = <<<_HTML_
                <li>请完整填写三处密码输入框</li>
_HTML_;
        }

        if ( $password !== $password_confirmation )
        {
                $error_html .= <<<_HTML_
                <li>两次输入密码不一致！请重新输入</li>
_HTML_;
        }

        if ( $verify_corrent_password &&
                ! JWUser::VerifyPassword($user_info['id'], $current_password) )
        {
                $error_html .= <<<_HTML_
    <li>当前密码输入错误，清除新输入</li>
_HTML_;
        }
    }

	/*
	 * Update User Databse
	 */
	if ( empty($error_html) )
	{
		if ( ! JWUser::ChangePassword($user_info['id'], $password_confirmation) )
		{
			$error_html = <<<_HTML_
<li>密码修改失败，请稍后再试。</li>
_HTML_;
			JWSession::SetInfo('error', $error_html);
		}
		else
		{
			$notice_html = <<<_HTML_
<li>密码修改成功！</li>
_HTML_;
			if ( !$is_web_user )
				JWUser::SetWebUser($user_info['idUser']);

			// 重设密码成功，现在清理掉重设密码的标志
			if ( $is_reset_password	)
				JWSession::GetInfo('reset_password');

			JWSession::SetInfo('notice', $notice_html);
		}

		header ( "Location: /wo/account/settings" );
	}
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<?php
if ( empty($error_html) )
    $error_html = JWSession::GetInfo('error');
if ( empty($notice_html) )
    $notice_html = JWSession::GetInfo('notice');

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">信息无法修改：<ul> $error_html </ul></div>
_HTML_;
}


if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
?>


<div id="container">
<?php JWTemplate::SettingTab() ;?>

<script type="text/javascript">
function updateLink(value){
    if( value.length > 0 ) {
        $('indexLink').href = '/' + value + '/';
        $('indexString').innerHTML = 'http://JiWai.de/' + value + '/';
    }else{
        $('indexLink').href = '/';
        $('indexString').innerHTML = 'http://JiWai.de/';
    }
}
</script>

<div class="tabbody">

<?php if (false == $is_reset_password ) { ?>
    <h2>修改帐号资料</h2>
    <form id="f" action="/wo/account/settings" method="post">
    <input type="hidden" name="commit_u" value="1"/>
    <fieldset>
    <table width="100%" cellspacing="3">
        <tr>
            <th valign="top">用户名：</th>
            <td width="250">
                <input name="user[nameScreen]" type="text" id="user_nameScreen" onKeyup='updateLink(this.value)' value="<?php echo $outInfo['nameScreen'];?>" />
            </td>
            <td class="note">用来登陆叽歪de（最少5个字符，不可以使用汉字、空格和特殊字符）</td>
        </tr>
        <tr>
            <th>你的首页：</th>
            <td><a id="indexLink" href="/<?php echo $outInfo['nameScreen']; ?>/"><span id="indexString">http://JiWai.de/<?php echo $outInfo['nameScreen']; ?>/</span></a></td>
            <td class="note">登录名将作为你的叽歪de主页地址</td>
        </tr>
        <tr>
            <th>姓名：</th>
            <td><input id="user_name" name="user[nameFull]" type="text" value="<?php echo $outInfo['nameFull']; ?>" /></td>
            <td class="note">你的真实名字，可以使用中文和空格</td>
        </tr>
        <tr>
            <th>Email：</th>
            <td><input id="user_email" name="user[email]" type="text" value="<?php echo $outInfo['email']; ?>" /></td>
            <td class="note">用于找回密码和接收通知</td>
        </tr>
    </table>
    </fieldset>

    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
    </div>

    </form>

<? } ?>

    <h2>修改帐号密码</h2>
    <form action="/wo/account/settings" method="post" id="f1">
        <input type="hidden" name="commit_p" value="1"/>
	    <fieldset>
	    <table width="100%" cellspacing="3">
	        <tr>
	            <th>当前密码：</th>
	            <td width="250"><input id="current_password" name="current_password" type="password" /></td>
	            <td class="note">至少6个字符，建议使用数字、符号、字母组合的复杂密码</td>
	        </tr>
	        <tr>
	            <th>新密码：</th>
	            <td><input id="password" name="password" type="password" /></td>
	            <td>&nbsp;</td>
	        </tr>
	        <tr>
	            <th>重复输入新密码：</th>
	            <td><input id="password_confirmation" name="password_confirmation" type="password" /></td>
	            <td>&nbsp;</td>
	        </tr>
	    </table>
	    </fieldset>
	    <div style=" padding:20px 0 0 160px; height:50px;">
	    	<a onclick="$('f1').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
	    	<a class="button2" href="/wo/"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-back.gif'); ?>" alt="返回" /></a>
	    </div>            
    </form>

</div>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>          
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
