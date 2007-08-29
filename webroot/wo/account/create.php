<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

if ( JWLogin::IsLogined() )
{
	JWLogin::Logout();
}

$name_len_min = 4;

$aUserInfo = array();
$JWErr = '';
if ( isset($_REQUEST['user'])
		&& isset($_REQUEST['user']['nameScreen']) && trim($_REQUEST['user']['nameScreen']) ){

	$aUserInfo = $_REQUEST['user'];
    if( empty( $aUserInfo['nameFull'] ) ) 
        $aUserInfo['nameFull'] = $aUserInfo['nameScreen'];

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
			$idUser = JWSns::CreateUser($aUserInfo);
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
					JWSns::FinishInvitation($idUser, $invitation_id);

				$inviter_id	= JWSession::GetInfo('inviter_id');
				if ( isset($inviter_id) )
					JWSns::FinishInvite($idUser, $inviter_id);

				$notice_html = <<<_HTML_
厉害! 感谢你明智地选择了叽歪de! 从今以后你就是组织的人了，如果有谁欺负你就报组织的名字!
不知道怎么叽歪de话，你可以先到这里<a href="http://help.jiwai.de/NewUserGuide" target="_blank">《新手手册》</a>来看看。
_HTML_;
				JWSession::SetInfo('notice', $notice_html);


				header("Location: /wo/account/regok");
				exit();
			}else{
				$public_timeline_url = JWTemplate::GetConst('UrlPublicTimeline');
				$JWErr .= <<<_POD_
<ul>
	<li>注册有可能未成功，请你重新提交用户注册信息。 </li>
	<li>如系统提示帐号、email重复请尝试<a href="/wo/login">登录</a>。 </li>
	<li>如果仍然存在问题，请通知我们开展除虫工作：<a href="mailto:bug@jiwai.de?subject=BUG">wo@jiwai.de</a>。 </li>
	<li>给你带来的不便我们深感抱歉，在你成功注册之前，你可以先浏览一下<a href="$public_timeline_url">大家都在做什么</a>。 </li>
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

JWDB::Close();
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="create">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">
<h2 title="Create a Free Twitter Account">免费注册叽歪de帐号</h2>

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
	var JWErr = "注册用户信息不完整，请你完善后再次提交：\n\n";
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
		//JWErr += "\t" + n + ". 请输入正确的邮件地址\n";
		//n++;
	}

	if ( !bValid )
		alert ( JWErr )
    
	return bValid;
}

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

<form id="f" action="/wo/account/create" enctype="multipart/form-data" method="post" name="f" onsubmit="return validate_form(this);">
<fieldset>
    <table width="550" border="0" cellspacing="15" cellpadding="0">
        <tr>
            <td width="70" align="right" valign="top">用户名</td>        
            <td width="240">
                <input id="user_nameScreen" name="user[nameScreen]" size="30" type="text" maxlength="16" value="<?php if(array_key_exists('nameScreen',$aUserInfo)) echo $aUserInfo['nameScreen'];?>" onKeyup="updateLink( this.value );"/><a href="/wo/account/complete">已经通过手机或IM注册过</a>
            </td>
            <td valign="top" class="note">用来登录叽歪de（不可含汉字、空格及特殊字符，最短5个字符） </td>
        </tr>
        <tr>
            <td align="right">你的首页</td>
            <td><a id="indexLink" href="/"><span id="indexString">http://jiwai.de/</span></a></td>
            <td class="note">登录名将作为你的首页的地址</td>
        </tr>
        <tr>
            <td align="right">姓　名</td>
            <td><input type="text" name="user[nameFull]" value="<?php if(array_key_exists('nameFull', $aUserInfo)) echo $aUserInfo['nameFull'];?>" /></td>
            <td class="note">你的真实姓名，可使用中文或空格</td>
        </tr>
        <tr>
            <td align="right">密　码</td>
            <td><input id="user_pass" type="password" name="user[pass]" /></td>
            <td class="note">至少6个字符，建议使用字母、数字、符号组合的复杂密码</td>
        </tr>
        <tr>
            <td align="right">再输一遍</td>
            <td><input id="user_pass_confirm" type="password" name="user[pass_confirm]" /></td>
            <td class="note">&nbsp;</td>
        </tr>
        <tr>
            <td align="right">Email</td>
            <td><input id="user_email" type="text" name="user[email]" value="<?php if(array_key_exists('email',$aUserInfo)) echo $aUserInfo['email'];?>"/></td>
            <td class="note">用于找回密码和接收通知</td>
        </tr>
    </table>
</fieldset>

<ul class="choise">
    <li>
        <input name="checkbox" type="checkbox" value="checkbox" checked="checked" /> 我已阅读并接受　<a href="http://help.jiwai.de/Tos" style="font-size:14px;">服务条款</a>
    </li>
</ul>
    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-regest.gif'); ?>" alt="注册" /></a>
    </div>            

</form>
<?php
//JWTemplate::container_ending();
?>
</div><!-- #container -->
<script type="text/javascript">
//<![CDATA[
//$('user_nameScreen').focus()
//]]>
</script>

<?php JWTemplate::footer() ?>

</body>
</html>
