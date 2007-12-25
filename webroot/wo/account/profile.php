<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_REQUEST['user'];
$outInfo = $user_info;
$has_photo = !empty($user_info['idPicture']);

if ( $new_user_info )
{
	if( false == isset($new_user_info['protected']) ) 
	{
		$new_user_info['protected'] = 'N';
	}

	$file_info = @$_FILES['profile_image'];

	$notice_html = null;
	$error_html = null;
	$pic_changed = true;

	if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
	   )
	{

		$user_named_file = '/tmp/' . $file_info['name'];

		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) )
		{
			$idPicture	= JWPicture::SaveUserIcon($user_info['id'], $user_named_file);
			if ( $idPicture )
			{
				preg_match('/([^\/]+)$/',$user_named_file,$matches);
				JWUser::SetIcon($user_info['id'],$idPicture);
				$pic_changed = true;
				JWSession::SetInfo('notice','头像修改成功。');
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');
				$pic_changed = false;
				$error_html = '上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="'.$contact_url.'">联系我们</a>';
				JWSession::SetInfo('error',$error_html);
			}

			@unlink ( $user_named_file );
		}
	}
	else if ( isset($file_info) 
			&& $file_info['error']>0 
			&& 4!==$file_info['error']
		)
	{
		// PHP upload error, except NO FILE(that mean user want to delete).
		switch ( $file_info['error'] )
		{
			case UPLOAD_ERR_INI_SIZE:
				JWSession::SetInfo('notice', '头像文件尺寸太大了，请将图片缩小分辨率后重新上载。');
				break;
			default:
				JWSession::SetInfo('notice','抱歉，你选择的图像没有上传成功，请重试。');
				break;
		}
	}

	$array_changed = array();
	if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
		if ($new_user_info['nameFull'] === '')
			$array_changed['nameFull'] = $outInfo['nameScreen'];
		elseif (JWUser::IsValidFullName($new_user_info['nameFull']))
			$array_changed['nameFull'] = $new_user_info['nameFull'];
	}
	if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
		if( $new_user_info['nameFull'] === '' )
			$array_changed['nameFull'] = $outInfo['nameScreen'];
		else
			$array_changed['nameFull'] = $new_user_info['nameFull'];
	}

	if( $new_user_info['protected'] != $outInfo['protected'] ) {
		$array_changed['protected'] = $new_user_info['protected'];
	}

	if( $new_user_info['url'] != $outInfo['url'] ) {
		$new_user_info['url'] = ltrim( $new_user_info['url'], '/' );
		if( $new_user_info['url'] && false == preg_match( '/^(http:|https:)/', strtolower($new_user_info['url']) ) ) {
			$new_user_info['url'] = 'http://' . $new_user_info['url'];
		}
		$array_changed['url'] = $new_user_info['url'];
	}

	if( $new_user_info['email'] != $outInfo['email'] ) {
		$array_changed['email'] = $new_user_info['email'];
	}

	if( $new_user_info['address'] != @$outInfo['address'] ) {
		$array_changed['address'] = $new_user_info['address'];
	}

	if( $new_user_info['bio'] != $outInfo['bio'] ) {
		if( $new_user_info['bio'] === '' ) {
			$array_changed['bio'] = $outInfo['nameFull'];
		} else {
			$array_changed['bio'] = $new_user_info['bio'];
		}
	}

	$new_location = intval(@$_REQUEST['province'])."-".intval(@$_REQUEST['city']);
	$new_location = trim($new_location);
	if( $new_location != $outInfo['location'] ) {
		$array_changed['location'] = $new_location;
	}

	if( count( $array_changed ) || $pic_changed ) {
		if( count( $array_changed ) ) {
			JWUser::Modify( $user_info['id'], $array_changed );
			JWSession::SetInfo('notice', '修改个人资料成功');
		}
		Header('Location: /wo/account/profile');
		exit(0);
	}
}

/*Procince and city id */
$pid = $cid =0;
@list($pid, $cid) = explode('-', $outInfo['location']);
?>
<html>
<head>
<?php JWTemplate::html_head() ?>

<script type="text/javascript">
function validate_form(form)
{
	var bValid = true;
	var JWErr = "个人信息填写有误，请你完善后再次提交：\n\n";
	var n = 1;

	if ( 0==$('user_email').value.length
				|| !$('user_email').value.match(/[\d\w._\-]+@[\d\w._\-]+\.[\d\w._\-]+/) ){
		$('user_email').className += " notice";
		bValid = false;
		JWErr += "\t" + n + ". 请输入正确的邮件地址\n";
		n++;
	}

	if ( !bValid )
		alert ( JWErr )
    
	return bValid;
}

window.jiwai_init_hook_location_setting = function()
{
	JWLocation.select('provinceSelect','citySelect',<?php echo intval($pid);?>,<?php echo intval($cid);?>); 
}
</script>
</head>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab('/wo/account/profile') ?>

<?php
if ( $has_photo )
{
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
}
else
{
    $photo_url = JWTemplate::GetAssetUrl('/img/stranger.gif');
}
?>

<div class="tabbody">
<h2>修改个人资料</h2>
<form id="f" enctype="multipart/form-data" method="post" class="validator" action="/wo/account/profile" >

<fieldset>
    <table width="100%" cellspacing="3">
    <tr>
        <th valign="top">姓名：</th>
        <td width="250"><input name="user[nameFull]" type="text" id="user_nameFull" value="<?php echo $outInfo['nameFull']; ?>" ajax="nameFull" alt="姓名"/><i></i></td>
        <td class="note">你的真实姓名，可使用中文和空格</td>
    </tr>
    <tr>
        <th valign="top">头像：</th>
        <td colspan="2">
            <img src="<?php echo $photo_url; ?>" width="96" height="96" align="left" class="imagb" />
            <span class="note" style="padding-left:5px;">选择图片文件：支持.jpg .gif .png的图片 </span>
            <input type="file" name="profile_image"  style="margin-left:110px; width:248px;"/>
            <span class="note" style="padding-left:5px;">最大可以上传 2M 大小的图片</span>
        </td>
    </tr>
    <tr>
        <th valign="top">自我介绍：</th>
        <td><textarea name="user[bio]" id="user_name" style="height:60px;" maxLength="200" alt="自我介绍"><?php echo htmlSpecialChars($outInfo['bio']);?></textarea></td>
        <td class="note">一句话的介绍，不超过200个字</td>
    </tr>
    <tr>
        <th>来自：</th>
        <td>
            <select id='provinceSelect' name="province" style="width:112px;" onChange="JWLocation.select('provinceSelect','citySelect', this.options[this.options.selectedIndex].value, 0);"></select>
            <select id='citySelect' name="city" style="width:112px;"></select>
        </td>
        <td class="note">选择所在地区</td>
    </tr>
    <tr>
        <th>地址：</th>
        <td><input name="user[address]" type="text" id="user_address" value="<?php echo $outInfo['address']; ?>"/></td>
        <td class="note">填写邮寄地址，便于寄发资料或赠品</td>
    </tr>
    <tr>
        <th>个人网址：</th>
        <td><input name="user[url]" type="text" id="user_url" value="<?php echo $outInfo['url'] ?>" ajax="url" null="true" alt="网址"/><i></i></td>
        <td class="note">可在叽歪中显示一个相关的网址或个人网站 </td>
    </tr>
    <tr>
        <th>Email：</th>
        <td><input name="user[email]" type="text" id="user_email" value="<?php echo $outInfo['email']?>" ajax="email" null="true" alt="Email"/><i></i></td>
        <td class="note">用于找回密码和接收通知</td>
    </tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    <tr>
        <th>隐私设置：</th>
        <td><input type="checkbox" id="protected" name="user[protected]" style="width:14px;border:none;display:inline;" value="Y" <?php if($outInfo['protected']=='Y') echo "checked";?>/> <label for="protect">只对我关注的人开放我的叽歪更新</label></td>
        <td class="note">选择这项设置，更新不会出现在<a href="/public_timeline/">叽歪广场</a>中</td>
    </tr>
    </table>
</fieldset>

    <div style=" padding:20px 0 0 160px; height:50px;">
		<input onclick="if(JWValidator.validate('f'))$('f').submit();return false;" type="button" class="submitbutton" value="保存"/>
    </div>            

</form>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>  
</div><!-- #container -->

<?php JWTemplate::footer() ?>
</body>
</html>
