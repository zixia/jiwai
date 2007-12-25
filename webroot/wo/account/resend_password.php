<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

if ( isset($_REQUEST['email']) )
{
	$email = $_REQUEST['email'];

	if ( JWUser::IsValidEmail($email, true) )
		$user_db_row = JWUser::GetUserInfo($email);
    else
    {
		$notice_html = <<<_HTML_
哎呀！您输入的邮件地址不合法！
_HTML_;
    }

	if ( !empty($user_db_row) )
	{
		JWSns::ResendPassword($user_db_row['idUser']);

		$notice_html = <<<_HTML_
重新设置你密码的说明已经发送到你的邮箱，请查收。
_HTML_;
		JWSession::SetInfo('notice', $notice_html);

		header("Location: " . JWTemplate::GetConst('UrlLogin') );
		exit(0);
	}

    if (empty($notice_html))
    {
        $notice_html = <<<_HTML_
哎呀！我们没有找到你的邮件地址！
_HTML_;
    }

    JWSession::SetInfo('notice', $notice_html);

}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head(); ?>
</head>

<body class="account" id="create">

<?php JWTemplate::accessibility(); ?>

<?php JWTemplate::header(); ?>

<!-- ul id="accessibility">
<li>
你正在使用手机吗？请来这里：<a href="http://m.JiWai.de/">m.JiWai.de</a>!
</li>
<li>
<a href="#navigation" accesskey="2">跳转到导航目录</a>
</li>
<li>
<a href="#side">跳转到功能目录</a>
</li>
</ul -->


<div id="container">
<?php JWTemplate::ShowActionResultTips(); ?>
    <p class="top">忘记了？</p>
    <div id="wtMainBlock">
        <div class="leftdiv">
            <span class="bluebold16">是否已经绑定了手机、MSN、QQ或Gtalk呢？</span>
            <p>如果是，请发送<span class="orange12">pass+空格+密码</span>，来重置密码<br />

            例如：pass abc123 </p>
        </div><!-- leftdiv -->
        <div class="rightdiv">
            <div class="login">
                <form id="f" action="/wo/account/resend_password" enctype="multipart/form-data" method="post" name="f">
                    <p>请输入你的 Email 地址，我们将把密码重设的链接发给你。</p>
                    <p class="black14">Email<input id="email" name="email" type="text" class="inputStyle" style="width:270px" />
                    </p>
                    <div style="overflow: hidden; clear: both; height:5px; line-height: 1px; font-size: 1px;"></div>
                    <p class="po"><input name="commit" type="submit" class="submitbutton" value="确 定" />

                </form>
                <div style="overflow: hidden; clear: both; height: 70px; line-height: 1px; font-size: 1px;"></div>
            </div><!-- login -->
        </div><!-- rightdiv -->
    </div><!-- #wtMainBlock -->
    <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
<script type="text/javascript">
    $('email').focus();
</script>
</body>
</html>

