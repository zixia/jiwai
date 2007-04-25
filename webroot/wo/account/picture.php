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

//var_dump($_REQUEST);

$user_info		= JWUser::GetCurrentUserInfo();

$has_photo		= !empty($user_info['photoInfo']);

//var_dump($file_info);
if ( isset($_REQUEST['save'] ) )
{
	$file_info = @$_FILES['profile_image'];
	
	if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
			)
	{
			
		$user_named_file = '/tmp/' . $file_info['name'];

		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) )
		{
			if ( JWFile::SaveUserPicture($user_named_file) )
			{
				preg_match('/([^\/]+)$/',$user_named_file,$matches);

				JWUser::SetPicture($matches[1]);


				$notice_html = <<<_HTML_
<li>头像修改成功！</li>
_HTML_;
				JWSession::SetInfo('notice',$notice_html);
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = <<<_HTML_
<li>上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
				JWSession::SetInfo('error',$error_html);
			}

			unlink ( $user_named_file );
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
	header('Location: ' . $_SERVER['SCRIPT_URL']);
	exit(0);
}
else if ( isset($_REQUEST['delete'] ) )
{
	// User set empty picture
	JWUser::SetPicture();
	$notice_html = <<<_HTML_
<li>头像删除成功。您将不会出现在<a href="<?php echo JWTemplate::GetConst('UrlPublicTimeline')?>">叽歪广场</a>中。</li>
_HTML_;
	JWSession::SetInfo('notice',$notice_html);

	header('Location: ' . $_SERVER['SCRIPT_URL']);
	exit(0);
}

?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="picture">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('picture'); ?>


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
			<div class="notice">头像未能上传：<ul> $error_html </ul></div>
_HTML_;
}


if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
else if ( !$has_photo )
{
	$public_timeline_url = JWTemplate::GetConst('UrlPublicTimeline');
echo <<<_HTML_
			<div class="notice"><ul>您现在没有头像，所以没有出现在<a href="$public_timeline_url">叽歪广场</a>中</ul></div>
_HTML_;

}
?>

				<form action="/wo/account/picture" enctype="multipart/form-data" method="post"><fieldset>
					<table cellspacing="0">
						<tr>
							<th>
								<label for="user_profile_image">
<?php
$arr_picture_info = JWUser::GetPictureInfo($user_info['photoInfo']);
if ( empty($arr_picture_info['name']) )
{	// we have no photo
	echo <<<_HTML_
									<img alt="$user_info[nameFull]" src="http://asset.jiwai.de/img/stranger.gif" style="vertical-align:middle"/>
_HTML_;
}
else	// we have photo
{
	echo <<<_HTML_
									<img alt="$user_info[nameFull]" src="/$user_info[nameScreen]/picture/thumb48?$arr_picture_info[time]" style="vertical-align:middle" />
_HTML_;
}
?>

								</label>
							</th>
							<td>
								<!--input id="user_profile_image_temp" name="user[profile_image_temp]" type="hidden" -->
								<input id="user_profile_image" name="profile_image" size="30" type="file" />
								<p><small>为保证您的图片效果，请不要上载太小和太大的图片。建议图片宽度在100-500之间，支持jpg、gif、png等文件格式。</small></p>
							</td>
						</tr>
						<tr>

							<th></th>
							<td>
								<input name="save" type="submit" value="保存" onclick="if(0==$('user_profile_image').value.length){alert('请先选择头像文件，然后再保存。如需删除头像，请点击<删除>按钮。'); return false}else{return true;}"/>
							</td>
						</tr>
					</table>
				</fieldset>
			</form>
<?php if ( !empty($arr_picture_info['name']) )
		echo <<<_HTML_
			<p><a href="?delete" onclick="return confirm('删除头像后您将无法出现在叽歪广场中，您确认删除头像图片吗？');"/>删除我的头像？</a></p>
_HTML_;
?>


		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
