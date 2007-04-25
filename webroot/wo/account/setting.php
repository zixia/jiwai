<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

require_once('../../jiwai.inc.php');
JWDebug::init();

JWUser::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();
$new_user_info	= @$_REQUEST['user'];

//var_dump($user_info);

if ( isset($new_user_info) )
{
	//die(var_dump($new_user_info));


	$nameFull	= trim(@$new_user_info['nameFull']);
    $nameScreen	= trim(@$new_user_info['nameScreen']);
    $email		= trim(@$new_user_info['email']);
    $url		= trim(@$new_user_info['url']);
    $bio		= trim(@$new_user_info['bio']);
    $location	= trim(@$new_user_info['location']);
    $protected	= @$new_user_info['protected'];

	$arr_changed 	= array();
	$error_html 	= null;

	if ( isset($nameFull) && $nameFull!=$user_info['nameFull'] )
	{
		$arr_changed['nameFull'] = $nameFull;
	}

	if ( isset($nameScreen) && $nameScreen!=$user_info['nameScreen'] )
	{
		$arr_changed['nameScreen'] = $nameScreen;

		if ( !JWUser::IsValidName($nameScreen) )
		{
			$error_html .= <<<_HTML_
<li>昵称 <strong>$nameScreen</strong> 需由最短为5位的字母、数字、下划线和小数点组成，且不能短于6个字符。</li>
_HTML_;
		}

		if ( JWUser::IsExistName($nameScreen) )
		{
			$error_html .= <<<_HTML_
<li>昵称 <strong>$nameScreen</strong> 已经被使用。</li>
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


	if ( isset($url) && $url!=$user_info['url'] )
	{
		$arr_changed['url'] = $url;
	}

	if ( isset($bio) && $bio!=$user_info['bio'] )
	{
		$arr_changed['bio'] = $bio;
	}

	if ( isset($location) && $location!=$user_info['location'] )
	{
		$arr_changed['location'] = $location;
	}

	if ( isset($protected) && $protected!=$user_info['protected'] )
	{
		$arr_changed['protected'] = $protected=='Y'?'Y':'N';
	}


//var_dump($arr_changed);
//die ( "[$error_html] " );
	/*
	 * Update User Databse
	 */
	if ( empty($error_html) 
			&& (!empty($arr_changed) ) )
	{
		if ( ! JWUser::Update($arr_changed) )
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

		header ( "Location: /wo/account/setting" );
	}
}


?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('account'); ?>

<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
{
	$notice_html	= JWSession::GetInfo('notice');
}

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
			<form action="/wo/account/setting" enctype="multipart/form-data" method="post">
				<fieldset>
					<table cellspacing="0">
					<tr>
						<th><label for="user_username">昵称：</label></th>
						<td>
							<input id="user_nameScreen" name="user[nameScreen]" size="30" type="text" 
								value="<?php echo isset($new_user_info)
														?$new_user_info['nameScreen']
														:$user_info['nameScreen']?>" />
							<p><small>昵称将作为你的叽歪de主页地址：
							(<a href="/<?php echo isset($new_user_info)
														?$new_user_info['nameScreen']
														:$user_info['nameScreen']?>">http://JiWai.de/<?php echo isset($new_user_info)
                                                       										? $new_user_info['nameScreen']
                                                        									: $user_info['nameScreen']?></a>).
							它应该是全站唯一的，只能够使用字母、数字和下划线。</small></p>

						</td>
					</tr>
					<tr>
						<th><label for="user_email">Email：</label></th>
						<td><input id="user_email" name="user[email]" size="30" type="text" value="<?php echo isset($new_user_info)
                                                        ? $new_user_info['email']
                                                        : $user_info['email']?>" /></td>
					</tr>

					<tr>
						<th><label for="user_name">姓名：</label></th>

						<td><input id="user_name" name="user[nameFull]" size="30" type="text" value="<?php echo isset($new_user_info)
                                                        ? $new_user_info['nameFull']
                                                        : $user_info['nameFull']?>" /></td>
					</tr>
	
					<tr>
						<th><label for="user_url">网址：</label></th>
						<td>
							<input id="user_url" name="user[url]" size="30" type="text" value="<?php echo isset($new_user_info)
                                                        ? $new_user_info['url']
                                                        : $user_info['url']?>" />
							<p><small>有Blog或者网上像册？将地址输入在这里吧。</small></p>

						</td>
					</tr>
					<tr>
						<th><label for="user_bio">简介：</label></th>
						<td>
							<input id="user_bio" maxlength="80" name="user[bio]" size="30" type="text" value="<?php echo isset($new_user_info)
                                                        ? $new_user_info['bio']
                                                        : $user_info['bio']?>" />
							<p><small>一句话（不超过70个汉字）的个人简介</small></p>
						</td>
					</tr>
					<tr>
						<th><label for="user_location">地址：</label></th>
						<td>
							<input id="user_location" name="user[location]" size="30" type="text" value="<?php echo isset($new_user_info)
                                                        ? $new_user_info['location']
                                                        : $user_info['location']?>" />
							<p><small>你生活在哪个省市？</small></p>
						</td>
					</tr>

					<tr>
						<th></th>
						<td>
<?php
//var_dump($new_user_info);
//var_dump($user_info);
?>
							<input <?php echo isset($new_user_info)
                                                        ? ('Y'==@$new_user_info['protected']?' checked ':'')
                                                        : ('Y'==$user_info['protected']?' checked ':'')?> id="user_protected" name="user[protected]" type="checkbox" value="Y" />
							<label for="user_protected">设为私密</label>

							<p><small>只允许被我加为好友的人看到我的更新。如果选择了这项设置，你将不会出现在
							<a href="<?php echo JWTemplate::GetConst('UrlPublicTimeline')?>">叽歪广场</a>中。</small></p>
						</td>

					</tr>
					<tr>
						<th></th>
						<td><input name="commit" type="submit" value="保存" /></td>
					</tr>

					</table>
				</fieldset>
			</form>

			<p><a href="/wo/account/delete">删除我的帐号？</a></p>


		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
