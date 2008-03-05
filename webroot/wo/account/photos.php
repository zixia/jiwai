<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$outInfo = $user_info;
$has_photo = !empty($user_info['idPicture']);

if ( !empty($_POST ) )
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
			$picture_id	= JWPicture::SaveUserIcon($user_info['id'], $user_named_file);
			if ( $picture_id )
			{
				preg_match('/([^\/]+)$/',$user_named_file,$matches);
				JWUser::SetIcon($user_info['id'],$picture_id);

				/* set user status picture */
				if ( null == $user_info['idPicture'] ) 
				{
					JWSns::SetUserStatusPicture( $user_info['id'], $picture_id );	
				}

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

	JWTemplate::RedirectToUrl();
}

?>
<html>
<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>
<?php
if ( $has_photo )
{
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
}
else
{
    $photo_url = JWTemplate::GetAssetUrl('/images/org-nobody-96-96.gif');
}
?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings" class="now">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" action="" enctype="multipart/form-data" method="post" name="f">
<p class="right14"><a href="/wo/account/settings">帐户信息</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/password">修改密码</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/photos" class="now">头像</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/profile">个人资料</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/interest">兴趣爱好</a></p>
	<div class="accountBox">
     <div class="accountHead"><a href="/<?php echo $user_info['nameUrl'];?>/"><img width="96" height="96" title="<?php echo $user_info['nameFull'];?>" src="<?php echo $photo_url;?>"/></a></div>
	  <div class="accountCont">
	   <p class="black15bold">上传新头像</p>
	   <p class="gray12">支持.jpg .gif .png的图片,最大可以上传 2M 大小的图片</p>
       <p>
         <input name="profile_image" type="file" class="inputStyle2" style=" margin:0;"/>
       </p>
       <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
	   <p><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></p>
	   </div><!-- accountCont -->
	   </div><!-- accountBox -->
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>
</body>
</html>
