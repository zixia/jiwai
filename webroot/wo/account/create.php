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
if (  isset($_REQUEST['user'])
		&& isset($_REQUEST['user']['nameScreen']) 
		&& trim($_REQUEST['user']['nameScreen']) ){

	$aUserInfo = $_REQUEST['user'];
    $nameScreen = $aUserInfo['nameScreen'];

    /**
     * 如果设备号正确，则修改指定用户的记录并退出
     */
    if (isset($_REQUEST['user']['DeviceNo'])
              && trim($_REQUEST['user']['DeviceNo']) )
    {
        $idUser = JWUser::GetUserInfo($nameScreen, 'idUser');
        if( !empty($idUser) ) 
        { 
            $newUserInfo = array (
                    'pass' => JWUser::CreatePassword($aUserInfo['pass']),
                    'email' => $aUserInfo['email']
                    );

            if (JWUser::Modify($idUser, $newUserInfo))
            {
                if ( $idUser && JWLogin::Login ( $idUser, true ) )
                {
                    JWBalloonMsg::CreateUser( $idUser );
                    JWUser::SetWebUser($idUser,true);
                    header("Location: /wo/");
                    exit();
                }
            }
        }   
    }
    /**
     * 如果无设备号，则创建新用户并退出
     */
	//JWDebug::trace($aUserInfo);
	if( empty( $aUserInfo['nameFull'] )|| !JWUser::IsValidFullName($aUserInfo['nameFull']) ) 
		$aUserInfo['nameFull'] = $aUserInfo['nameScreen2'];

	$email = '';

	$aValid = array (
				'nameScreen' => JWUser::IsValidName( $nameScreen ),
				'pass' => ( strlen($aUserInfo['pass'])>0 
						&& $aUserInfo['pass']===$aUserInfo['pass_confirm'] ),
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
			$aUserInfo['ip'] = JWRequest::GetIpRegister();
            // FIXME JWSns::Create
			//$idUser = JWSns::Create($aUserInfo);
			$idUser = JWUser::Create($aUserInfo);
            JWBalloonMsg::CreateUser( $idUser );

			if ( $idUser && JWLogin::Login ( $idUser, true ) )
			{
/*
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
*/                    

/*
				$notice_html = <<<_HTML_
厉害! 感谢你明智地选择了叽歪de! 从今以后你就是组织的人了，如果有谁欺负你就报组织的名字!
不知道怎么叽歪de话，你可以先到这里<a href="http://help.jiwai.de/NewUserGuide" target="_blank">《新手手册》</a>来看看。
_HTML_;
				JWSession::SetInfo('notice', $notice_html);
*/

//				header("Location: /wo/account/regok");
                JWUser::SetWebUser($idUser,true);
				header("Location: /wo/");
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

if ( isset($_REQUEST['user'])
		&& isset($_REQUEST['user']['nameScreen3']) 
		&& trim($_REQUEST['user']['nameScreen3']) )
{
	$aUserInfo = $_REQUEST['user'];
    $nameScreen = $aUserInfo['nameScreen3'];

    $idUser = JWUser::GetUserInfo($nameScreen, 'idUser');
     
    if( !empty($idUser) ) 
    { 
        $newUserInfo = array (
                'pass' => JWUser::CreatePassword($aUserInfo['pass3']),
                'email' => $aUserInfo['email3']
                );

        if (JWUser::Modify($idUser, $newUserInfo))
        {
			if ( $idUser && JWLogin::Login ( $idUser, true ) )
            {
                JWBalloonMsg::CreateUser( $idUser );
                JWUser::SetWebUser($idUser,true);
				header("Location: /wo/");
				exit();
            }
        }
    }   

    return false;               
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

<div id=g style="overflow:hidden;display:float;height:390px;border-width:5px;border-color:#FF0000;">
<div id=g1 style="display:block;">
<TABLE cellSpacing=0 cellPadding=0 width=560 border=0>
<TBODY>
<TR>
<TD>
<div style="border:1px solid #F2F2F2;">
<div class="RegLeftTable" >
<div class="RegLeftTitle">第一次接触叽歪?</div><br/>
<div align="center" class="RegText" style="height:30px;margin-bottom:10px;">请先阅读和接受我们的<a href="http://help.jiwai.de/Tos" target="_blank">服务条款</a>。</div>
<div align="center" ><input type="button" id="StartReg" class="submitbutton" style="width:135px;border-style:none;" onclick="JiWai.slideTo('g','slideOut12',500,'');" value="接受，开始注册吧"></div>
</div>
</div>
</TD>
<TD width="80" ><div class="RegMiddleTitle">或者</div>  
</TD>
<TD>
<div style="border:1px solid #F2F2F2;">
<div class="RegRightTable" >
<div class="RegRightTitle">已经通过手机、QQ、MSN或者Gtalk使用过叽歪了?</div>
<div class="RegText">通过这里，你可以登录到自己的帐户，修改密码完成注册，浏览过去的叽歪条目。</div>
<div class="RegText">号　码　<INPUT style="width:170px;height:20px;display:inline;" id=user_DeviceNo1 alt=号码 title=号码 maxLength=64 size=20 name=user_DeviceNo1 value="" ajax2="DeviceNoAndNameScreen"></div>
<div class="RegRightTips" align="right">手机号码，或是QQ号码，MSN邮件地址等</div>
<div class="RegText">用户名　<INPUT style="width:170px;height:20px;display:inline;" id=user_nameScreen1 alt=用户名 title=用户名 maxLength=16 size=20 name=user_nameScreen1 value="" ajax2="DeviceNoAndNameScreen" onblur="JWValidator.onNameOrDeviceBlur();"></div>
<div class="RegRightTips" align="right">你通过上述设备所注册的叽歪名字</div>
<div class="RegRightTips" align="right" style="margin-top:-10px;">不记得了？发送whoami或woshishui查看</div>
<div><input type="button" class="submitbutton" style="width:120px;margin-left:50px;border-style:none;" onclick="if(JWValidator.validate1('RegTips','user_DeviceNo1','user_nameScreen1'))JiWai.slideTo('g','slideOut13',500 ,'horizontal');" value="完成注册"></div>
<div id=RegTips class="RegErrorTips" style="display:none;"></div>
</div>
</div>
</TD>
</TR>
</TABLE>
</div>
<div id=g2 style="display:none;">
<form id="f2" action="/wo/account/create" enctype="multipart/form-data" method="post" name="f2" >
<fieldset>
    <table width="550" border="0" cellspacing="15" cellpadding="0">
        <tr>
            <td align="right">Email</td>
            <td><input id="user_email" type="text" name="user[email]" value="" ajax="email" alt="Email"/><i></i></td>
            <td class="note">用于找回密码和接收通知</td>
        </tr>
        <tr>
            <td align="right">密码</td>
            <td><input id="user_pass" type="password" name="user[pass]" alt="密码" minLength="6" maxLength="16" /><i></i></td>
            <td class="note">至少6个字符</td>
        </tr>
        <tr>
            <td align="right">确认密码</td>
            <td><input id="user_pass_confirm" type="password" name="user[pass_confirm]" alt="确认密码" compare="user_pass" minLength="6" maxLength="16" onblur="JWValidator.onPassBlur('user_pass_confirm');"/><i></i></td>
            <td ></td>
        </tr>
        <tr>
            <td width="70" align="right" valign="top">用户名</td>        
            <td width="240" colspan="1">
               <input id="user_nameScreen" style="display:inline;margin-right:20px;" name="user[nameScreen]" size="30" type="text" minLength="5" maxLength="16" value="" alt="用户名" title="用户名" onkeydown="if(event.keyCode == 13)$('reg').onclick();" onblur="JWValidator.onNameOrDeviceBlur2();" ajax2="DeviceNoAndNameScreen2"/><i></i><td><span class="note">字母、数字或汉字，至少5位</span></td>
            </td>
        </tr>
        <tr>
           <td></td>
           <td colspan="2"><i id="RegTips2" ></i></td>
        </tr>
        <div id="DevNo" style="display:none;">
        <tr>
            <td width="70" align="right" valign="top"><div id="DevNo21" style="display:none;"></div></td>        
            <td width="240">
            <div id="DevNo22" style="display:none;">
                <input id="user_DeviceNo" name="user[DeviceNo]" size="30" type="text" maxLength="64" value="" title="号码" alt="号码" ajax2="DeviceNoAndNameScreen2" null="true" onblur="JWValidator.onNameOrDeviceBlur2();" onkeydown="if(event.keyCode == 13)$('reg').onclick();"/><i></i>
            </div>
            </td>
            <td valign="top" class="note"><div id="DevNo23" style="display:none;"></div></td>
        </tr>
        </td></tr>
        </div>
    </table>
</fieldset>

    <div style=" padding:20px 0 0 270px; height:50px;">
    	<a id=reg onclick="if(JWValidator.validate2('f2','RegTips2'))$('f2').submit();return false;" class="button"  alt="注册" title="注册" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-regest.gif'); ?>"/></a>
        <a class="RegReturn1" href="javascript:void(0);" onclick="JiWai.slideTo('g','slideOut21',500 ,'');"><< 返回</a>
    </div>            

</form>
</div>
<div id=g3 style="display:none;">
        <form id="f" action="/wo/account/create" enctype="multipart/form-data" method="post" name="f" >
<table width="700"><tr><td>
<div style="border:1px solid #C2C2C2;">
<div class="RegRightTable" style="background-color:#C2C2C2;">
<div class="RegRightTitle">　已经通过手机、QQ、MSN或者Gtalk使用过叽歪了?</div>
<div class="RegText">　通过这里，你可以登录到自己的帐户，修改密码完成注册，浏览过去的叽歪条目。</div>
<div class="RegText">号　码　<INPUT style="width:170px;height:20px;display:inline;background-color:#CDCDCD;" id=user_DeviceNo3 alt=号码 title=号码 maxLength=64 size=20 name=user_DeviceNo3 value="" readonly="true"></div>
<div class="RegRightTips">手机号码，或是QQ号码，MSN邮件地址等</div>
<div class="RegText">用户名　<INPUT style="width:170px;height:20px;display:inline;background-color:#CDCDCD;" id=user_nameScreen3 alt=用户名 title=用户名 maxLength=16 size=20 name=user[nameScreen3] value="" readonly="true"></div>
<div class="RegRightTips" align="right">你通过上述设备所注册的叽歪名字</div><div class="RegRightTips">不记得了？发送whoami或woshishui查看</div>
<div><input type="button" class="submitbutton" style="width:120px;margin-left:50px;" disabled="true" value="完成注册"></div>
<div id=RegTips3 class="RegErrorTips"></div>
</div>
</div>
</td>
<td width="100"></td>
<td>
<fieldset>
    <table border="0" cellspacing="15" cellpadding="0">
        <tr>
            <td align="right">Email　</td>
            <td ><input id="user_email3" type="text" name="user[email3]" value="" ajax="email" alt="Email"/><i></i><span class="note">用于找回密码和接收通知</span></td>
        </tr>
        <tr>
            <td align="right">密　码　</td>
            <td><input id="user_pass3" type="password" name="user[pass3]" alt="密码" minLength="6" maxLength="16"/><span class="note">至少6个字符</span><i></i></td>
        </tr>
        <tr>
            <td align="right">确认密码</td>
            <td><input id="user_pass_confirm3" type="password" name="user[pass_confirm3]" alt="确认密码" compare="user_pass3" minLength="6" onkeydown="if(event.keyCode == 13)$('reg3').onclick();" maxLength="16" onblur="JWValidator.onPassBlur('user_pass_confirm3');"/><i></i></td>
        </tr>
        <tr>
            <td><a class="RegReturn2" href="javascript:void(0);" onclick="JiWai.slideTo('g','slideOut31',500 ,'horizontal');"><< 返回</a></td>
            <td><a id=reg3 onclick="if(JWValidator.validate2('f'))$('f').submit();return false;" class="button" style="margin-left:50px!important;margin-left:30px;" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-regest.gif'); ?>" alt="注册" title="注册"/></a></td>
        </tr>
    </table>
</fieldset>

</td></tr>
</table>
</form>
</div>
</div>
</div>
<?php
//JWValidator.validate('f')
//JWTemplate::container_ending();
?>
</div><!-- #container -->
<script type="text/javascript">
//<![CDATA[
//$('user_nameScreen').focus()
//]]>
</script>

<?php JWTemplate::footer() ?>
<?php
function IsAddressBelongsToName($address,$name)
{
	if ( empty($address) || empty($name) )
		return false;

	if ( preg_match('/^\d/',$name) )
		return false;

	$user_row	 	= JWUser::GetUserInfo($name);

	if ( empty($user_row) )
		return false;

	$device_row		= JWDevice::GetDeviceRowByUserId($user_row['idUser']);

	if ( empty($device_row) )
		return false;

	$ims = array_keys($device_row);

	foreach ( $ims as $im )
	{
		if ( $address==$device_row[$im]['address'] )
			return true;
	}

	return false;
}
?>
<script defer="true">
	JWValidator.init('f');
	JWValidator.init('f2');
</script>
</body>
</html>
