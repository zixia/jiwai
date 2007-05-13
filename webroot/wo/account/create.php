<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');


$name_len_min = 5;

$aUserInfo = array();
$JWErr = '';
if ( isset($_REQUEST['user'])
		&& isset($_REQUEST['user']['nameScreen']) ){

	$aUserInfo = $_REQUEST['user'];

	//echo "<pre>";var_dump($_FILES); die(var_dump($_REQUEST));
	//JWDebug::trace($aUserInfo);

	$nameScreen = $aUserInfo['nameScreen'];
	$email = '';

	$aValid = array (	'nameScreen'	=>	JWUser::IsValidName( $nameScreen )
						// CAPTCHA , 'captcha'		=>	JWCaptcha::validate( $_REQUEST['key'] )
						, 'pass'		=>	strlen($aUserInfo['pass'])>0 && $aUserInfo['pass']===$aUserInfo['pass_confirm']
					);


	$aValid['email'] = true;
	if ( array_key_exists('email',$aUserInfo) ){
		$email = $aUserInfo['email'];

		if ( false && $email ){
			$aValid['email'] = JWUser::IsValidEmail( $email, false );
		}
	}else{
		$email = '';
	}


	$bValid = array_reduce ( $aValid, create_function(
										'$bResult, $v','return $bResult&&$v;') 
										, true
							);

	//JWDebug::trace($valid);
	$aExist = array();

	if ( $bValid ){
		$aExist = array (	'nameScreen'	=>	JWUser::IsExistName( $nameScreen )
							, 'email'		=>	empty($email) ? false : JWUser::IsExistEmail( $email )
					);
		if ( !$aExist['nameScreen'] && !$aExist['email'] )
		{
			$idUser = JWUser::Create($aUserInfo);
			if ( $idUser && JWLogin::Login ( $idUser, true ) )
			{

				// after a user create his account the first time, we try to save the pict he uploaded, and ignore errors.
				$file_info = @$_FILES['profile_image'];
    
    			if ( isset($file_info)
            			&& 0===$file_info['error'] 
            			&& preg_match('/image/',$file_info['type']) )
    			{                       
        			$user_named_file = '/tmp/' . $file_info['name'];
        			if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) )
        			{
            			$idPicture = JWPicture::SaveUserIcon($idUser,$user_named_file);
            			if ( $idPicture )
            			{   
   				            JWUser::SetIcon($idUser,$idPicture);
						}
					}
				}

				$invitation_id	= JWSession::GetInfo('invitation_id');
				
				if ( isset($invitation_id) )
				{
					JWInvitation::Register($invitation_id, $idUser);


					$invitation_rows		= JWInvitation::GetInvitationRowsByIds(array($invitation_id));
					$inviter_id				= $invitation_rows[$invitation_id]['idUser'];

					$reciprocal_user_ids	= JWInvitation::GetReciprocalUserIds($invitation_id);
					array_push( $reciprocal_user_ids, $inviter_id );

					// 互相加为好友
					JWSns::AddFriends( $idUser, $reciprocal_user_ids, true );
				}

				header("Location: /wo/invitations/invite");
				exit();
			}else{
				$public_timeline_url = JWTemplate::GetConst('UrlPublicTimeline');
				$JWErr .= <<<_POD_
<ul>
	<li>注册有可能未成功，请您重新提交用户注册信息。 </li>
	<li>如系统提示帐号、email重复请尝试<a href="/wo/login">登录</a>。 </li>
	<li>如果仍然存在问题，请通知我们开展除虫工作：<a href="mailto:bug@jiwai.de?subject=BUG">wo@jiwai.de</a>。 </li>
	<li>给您带来的不便我们深感抱歉，在您成功注册之前，您可以先浏览一下<a href="$public_timeline_url">大家都在做什么</a>。 </li>
</ul>
_POD_;
			}
			
		}else{ // email or nameScreen already exist
			$JWErr .= "<ul>\n";
			if ( $aExist['nameScreen'] ){
				$JWErr .= "\t<li>帐号( <em>" . htmlspecialchars($aUserInfo['nameScreen']) . "</em> )已经被使用</li>\n";
			}
			if ( $aExist['email'] ){
				$JWErr .= "\t<li>Email ( <em>" . htmlspecialchars($aUserInfo['email']) . "</em> )已经被使用</li>\n";
			}
			$JWErr .= "</ul>\n";
		}
	
	}else{ // input not valid:
		$JWErr .= "<ul>\n";
		if ( !$aValid['nameScreen'] )
			if ( preg_match('/^\d+$/', $nameScreen) )
				$JWErr .= "\t<li>帐号首字符不可为数字。如果是QQ号码，建议将 $aUserInfo[nameScreen] 注册为： <em> QQ$nameScreen </em></li>\n";
			else if ( preg_match('/^\d/', $nameScreen) )
				$JWErr .= "\t<li>帐号( <em>" . htmlspecialchars($aUserInfo['nameScreen']) . "</em> )的第一个字符不可以为数字</li>\n";
			else
				$JWErr .= "\t<li>帐号( <em>" . htmlspecialchars($aUserInfo['nameScreen']) . "</em> )含有特殊字符</li>\n";

		if ( strlen($aUserInfo['nameScreen']) < $name_len_min )
			$JWErr .= "\t<li>帐号( <em>" . htmlspecialchars($aUserInfo['nameScreen']) . "</em> )不可少于最短5个字符</li>\n";
			
		if ( !$aValid['email'] )
			$JWErr .= "\t<li>Email ( <em>" . htmlspecialchars($aUserInfo['email'])  ."</em> )不正确</li>\n";
	
/*	CAPTCHA 暂不检查注册码，等待 memcache 记录启用，能够识别多次重复注册时再启用
		if ( !$aValid['captcha'] ){
			$JWErr .= "\t<li>校验码输入错误，请重新输入</li>\n";
		}
*/
		if ( !$aValid['pass'] ){
			$JWErr .= "\t<li>密码两次输入不一致（注意密码不可为空）</li>\n";
		}
		$JWErr .= "</ul>\n";
	}

}

JWDB::close();
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="create">

<?php JWTemplate::html_head() ?>
	
<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">
			

			<h2 title="Create a Free Twitter Account">免费注册叽歪de帐号</h2>

			<p>已经是叽歪de手机短信用户？
				<a href="/wo/account/complete">请来这里</a>，我们将帮助您获取Web帐号。
			</p>

<?php
if ( !empty($JWErr) ){
	echo <<<_POD_
			<div class="notice">
				$JWErr
			</div>
_POD_;
}
?>

			<script type="text/javascript">
function validate_form(form)
{
	var bValid = true;
	var JWErr = "注册用户信息不完整，请您完善后再次提交：\n\n";
	var n = 1;

	if ( 0==$('user_nameScreen').value.length
				|| $('user_nameScreen').value.match(/ /) 
				|| $('user_nameScreen').value.length < <?php echo $name_len_min?>)
	{
		$('user_nameScreen').className += " notice";
		bValid = false;
		JWErr += "\t" + n + ". 请输入不含空格，最短5位的帐号\n";
		n++;
	}

	if ( 0==$('user_pass').value.length ){
		$('user_pass').className += " notice";
		bValid = false;
		JWErr += "\t" + n + ". 请创建密码\n";
		n++;
	}
	if ( 0==$('user_pass_confirm').value.length ){
		$('user_pass_confirm').className += " notice";
		bValid = false;
		JWErr += "\t" + n + ". 请再次输入密码\n";
		n++;
	}
	if ( $('user_pass').value != $('user_pass_confirm').value ){
		$('user_pass').className += " notice";
		$('user_pass_confirm').className += " notice";
		bValid = false;
		JWErr += "\t" + n + ". 两次输入密码不一致\n";
		n++;
	}

/* CAPTCHA
	if ( $('key') ){
		if ( 0==$('key').value.length ){
			$('key').className += " notice";
			bValid = false;
			JWErr += "\t" + n + ". 请输入字母验证码\n";
			n++;
		}else if ( $('key').value.match(/\d/) ){
			bValid = false;
			JWErr += "\t" + n + ". 验证码中只有字母，没有出现数字\n";
			n++;
		}
	}
*/


	if ( 0==$('user_email').value.length
				|| !$('user_email').value.match(/[\d\w._\-]+@[\d\w._\-]+\.[\d\w._\-]+/) ){
		$('user_email').className += " notice";
		//bValid = false;
		JWErr += "\t" + n + ". 请输入正确的邮件地址\n";
		n++;
	}

	if ( !bValid )
		alert ( JWErr )

	return bValid;
}
			</script>
			<form action="/wo/account/create" enctype="multipart/form-data" method="post" name="f" onsubmit="return validate_form(this);">
				<fieldset>
					<table cellspacing="0">
						<tr>
							<th><label for="user_nameScreen">选择帐号：</label></th>

							<td><input id="user_nameScreen" name="user[nameScreen]" size="30" type="text" value="<?php if(array_key_exists('nameScreen',$aUserInfo)) echo $aUserInfo['nameScreen'];?>" /> 
								<small>用来登录<em>叽歪de</em>（不可含汉字、空格及特殊字符，最短5个字符）</small>
							</td>
						</tr>
						<tr>
							<th><label for="user_nameFull">名字：</label></th>

							<td><input id="user_nameFull" name="user[nameFull]" size="30" type="text" value="<?php if(array_key_exists('nameFull',$aUserInfo)) echo $aUserInfo['nameFull'];?>" />
								<small>可含汉字和空格</small>
							</td>
						</tr>
						<tr>
							<th><label for="user_pass">创建密码：</label></th>

							<td><input id="user_pass" name="user[pass]" type="password" /> <small>至少6个字符</small></td>
						</tr>
						<tr>
							<th><label for="user_pass_confirm">再次输入密码：</label></th>

							<td><input id="user_pass_confirm" name="user[pass_confirm]" size="30" type="password" /></td>
						</tr>
							<tr>
							<th><label for="user_email">Email 地址:</label></th>

							<td><input id="user_email" name="user[email]" size="30" type="text" value="<?php if(array_key_exists('email',$aUserInfo)) echo $aUserInfo['email'];?>"/> <small>以防遗忘密码</small></td>
						</tr>
						<tr>
							<th>
								<label for="user_profile_image">
									头像图片：
								</label>
							</th>
						<td>
							<input id="user_profile_image" name="profile_image" size="30" type="file" value="浏览"/>
							<p><small>最小尺寸为48x48（jpg, gif, png）。（如果你上传头像图片，你将会出现在“<a href="/<?php echo JWTemplate::GetConst('UrlPublicTimeline')?>">叽歪广场</a>”中）</small></p>
						</td>
					</tr>
					<tr>
						<th>
						</th>
						<td>
							<input id="user_protected" name="user[protected]" type="checkbox" value="1" <?php if(array_key_exists('protected',$aUserInfo)) echo ' checked ';?>  />
							<!--input name="user[protected]" type="hidden" value="N" /--> 
							<label for="user_protected">不公开我的更新</label>
							<p><small>
								只允许被我加为好友的人阅读我的更新。如果选中上面的方框，你的更新将不会出现在“叽歪de大家”中。
							</small></p>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<br />
							<p title="By default, we&rsquo;ll send you occasional Twitter news by email. It&rsquo;s extremely
							easy to unsubscribe at any time (one click in the email).">
							我们有时会通过E-mail来通知你一些关于叽歪de消息，你可以很容易地取消订阅（在email中点击一下即可）。
							</p>

							<p>加入叽歪de之前，请确认你在13周岁以上，并接受<a href="/tos" target="_blank">服务条款</a>。</p>
						</td>
					</tr>		
					<!--tr> CAPTCHA
						<th></th>
						<td>
							<input id="digest" name="digest" type="hidden" value="zixia_digest" />

							<label for="key">
  								<img border="2" src="/wo/captcha" width="164" height="54" />
  								<br />验证码 - 输入上面所显示的字母（不含数字）：
							</label>
							<p><input type="text" id="key" name="key" value="" /></p>
						</td>
					</tr-->
					<tr>
						<th></th>
						<td><br /><input name="commit" type="submit" value="创建我的帐号" /></td>
					</tr>
				</table>
			</fieldset>
		</form>
		<script type="text/javascript">
//<![CDATA[
$('user_nameScreen').focus()
//]]>
		</script>
 
		</div><!-- wrapper -->
	</div><!-- content -->

<?php 

$arr_menu 			= array(	array ('head'	, array('<h3>已经是注册用户? 请直接登陆:</h3>'))
								, array ('login'		, array( array('focus'	=> false) ))
							);


JWTemplate::sidebar($arr_menu, null) ;
?>
	
</div><!-- #container -->

<hr class="separator" />
		
<?php JWTemplate::footer() ?>

</body>
</html>
