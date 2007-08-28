<?php
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

//var_dump($_REQUEST);

$user_info		= JWUser::GetCurrentUserInfo();
$new_user_info = @$_REQUEST['user'];
$outInfo = $user_info;
$has_photo		= !empty($user_info['idPicture']);

//echo "<pre>"; die(var_dump($user_info));
//var_dump($file_info);
if ( $new_user_info )
{

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

				$notice_html = <<<_HTML_
<li>头像修改成功！</li>
_HTML_;

				JWSession::SetInfo('notice',$notice_html);
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');

                $pic_changed = false;

				$error_html = <<<_HTML_
<li>上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
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
				$error_html = <<<_HTML_
<li>头像文件尺寸太大了，请将图片缩小分辨率后重新上载。<li>
_HTML_;
				JWSession::SetInfo('notice',$error_html);
				break;
			default:
				throw new JWException("upload error $file_info[error]");
				break;
		}
	}

    $array_changed = array();
    if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
        if( $new_user_info['nameFull'] === '' )
            $array_changed['nameFull'] = $outInfo['nameScreen'];
        else
            $array_changed['nameFull'] = $new_user_info['nameFull'];
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

    $new_location = @$_REQUEST['provice']." ".@$_REQUEST['city']." ".@$_REQUEST['country'];
    $new_location = trim($new_location);
    if( $new_location != $outInfo['location'] ) {
        $array_changed['location'] = $new_location;
    }

    if( count( $array_changed ) || $pic_changed ) {
        if( count( $array_changed ) ) {
            JWUser::Modify( $user_info['id'], $array_changed );
        }
        Header('Location: /wo/account/profile');
        exit(0);
    }
}

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
</script>
</head>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab('/wo/account/profile') ?>

<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
	$notice_html	= JWSession::GetInfo('notice');

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">资料未能修改：<ul> $error_html </ul></div>
_HTML_;
}

if ( !empty($notice_html) )
{
		echo <<<_HTML_
			<div class="notice"> $notice_html </div>
_HTML_;
}

?>

<?php
if ( $has_photo ){
    // we have photo
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
}else{
    // we have no photo
    $photo_url = JWTemplate::GetAssetUrl('/img/stranger.gif');
}
?>

<div class="tabbody">
<h2>修改个人资料</h2>
<form id="f" enctype="multipart/form-data" method="post" action="/wo/account/profile" >

<fieldset>
    <table width="100%" cellspacing="3">
    <tr>
        <th valign="top">姓名：</th>
        <td width="250"><input name="user[nameFull]" type="text" id="user_nameFull" value="<?php echo $outInfo['nameFull']; ?>" /></td>
        <td class="note">你的真实姓名，可使用中文和空格</td>
    </tr>
    <tr>
        <th valign="top">头像：</th>
        <td colspan="2">
            <img src="<?php echo $photo_url; ?>" width="96" height="96" align="left" class="imagb" />
            <span class="note" style="padding-left:5px;">选择图片文件：支持.jpg .gif .png的图片 </span>
            <input type="file" name="profile_image"  style="margin-left:110px; width:248px;"/>
        </td>
    </tr>
    <tr>
        <th valign="top">自我介绍：</th>
        <td><textarea name="user[bio]" id="user_name" style="height:60px;"><?php echo htmlSpecialChars($outInfo['bio']);?></textarea></td>
        <td class="note">一句话的介绍，不超过70个字</td>
    </tr>
    <!-- tr>
        <th>来自：</th>
        <td>
            <label>
            <select name="province" style="width:75px;"></select>
            <select name="city" style="width:75px;"></select>
            <select name="country" style="width:75px;"></select>
            </label>
        </td>
        <td class="note">选择所在地区</td>
    </tr -->
    <tr>
        <th>地址：</th>
        <td><input name="user[address]" type="text" id="user_address" value="<?php echo $outInfo['address']; ?>"/></td>
        <td class="note">填写邮寄地址，便于寄发资料或赠品</td>
    </tr>
    <tr>
        <th>个人网址：</th>
        <td><input name="user[url]" type="text" id="user_url" value="<?php echo $outInfo['url'] ?>" /></td>
        <td class="note">可在叽歪中显示一个相关的网址或个人网站 </td>
    </tr>
    <tr>
        <th>Email：</th>
        <td><input name="user[email]" type="text" id="user_email" value="<?php echo $outInfo['email']?>"/></td>
        <td class="note">用于找回密码和接收通知</td>
    </tr>
    </table>
</fieldset>

    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
    </div>            


</form>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>  
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
