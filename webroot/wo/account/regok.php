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
$has_photo		= !empty($user_info['idPicture']);
$protected      = $user_info['protected'] == 'Y';

 if ( $has_photo ){
    // we have photo
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb48');
}else{
    // we have no photo
    $photo_url = JWTemplate::GetAssetUrl('/img/stranger.gif');
}

//echo "<pre>"; die(var_dump($user_info));
//var_dump($file_info);
//var_dump($_POST);
if ( isset($_POST['save_x'] ) ) 
{

    $protected = $_POST['protected'];

    JWUser::Modify( $user_info['id'], array(
                'protected' => $protected,
            ));

	$file_info = @$_FILES['profile_image'];
	if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
			) {
			
		$user_named_file = '/tmp/' . $file_info['name'];

		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) ) {
			$idPicture	= JWPicture::SaveUserIcon($user_info['id'], $user_named_file);

			if ( $idPicture ) {
				preg_match('/([^\/]+)$/',$user_named_file,$matches);

				JWUser::SetIcon($user_info['id'],$idPicture);


				$notice_html = <<<_HTML_
<li>头像修改成功！</li>
_HTML_;
				JWSession::SetInfo('notice',$notice_html);
	            header('Location: /wo/account/invite');
                exit(0);
			} else {
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = <<<_HTML_
<li>上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
				JWSession::SetInfo('error',$error_html);
                header('Location: /wo/account/regok');
                exit(0);
			}

			@unlink ( $user_named_file );
		}
	} else if ( isset($file_info) && $file_info['error']>0 && 4!==$file_info['error']) {
		// PHP upload error, except NO FILE(that mean user want to delete).
		switch ( $file_info['error'] ) {
			case UPLOAD_ERR_INI_SIZE:
				$error_html = <<<_HTML_
<li>头像文件尺寸太大了，请将图片缩小分辨率后重新上载。<li>
_HTML_;
				JWSession::SetInfo('notice',$error_html);
                header('Location: /wo/account/regok');
                exit(0);
				break;
			default:
				throw new JWException("upload error $file_info[error]");
				break;
		}
	}

	header('Location: /wo/account/invite');
	exit(0);
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="regok">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">

<h2>恭喜你注册成功！</h2>
<?php JWTemplate::ShowActionResultTips(); ?>

<p>你好，<?php echo htmlSpecialChars($user_info['nameScreen']); ?>，要<a href="/wo/">马上开始叽歪</a>吗？你可以先：</p>
<p><strong>上传头像图片</strong></p>

<form id="f" action="/wo/account/regok" method="POST" enctype="multipart/form-data">
<input type="hidden" name="save_x" value="1"/>
<table width="500" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="72"><img src="<?php echo $photo_url; ?>" width="48" height="48" class="imagb"  /></td>
        <td>选择图片文件 <span class="note">支持.jpg .gif .png的图像</span><input type="file" name="profile_image" style="width: 380px;" /></td>
    </tr>
</table>

<div  style="height:1px; overflow:hidden; background-color:#C2C2C2; margin:20px auto 0 auto; clear:both; width:500px;"></div>
<p><strong>消息私密设置</strong></p>

<ul class="choise">
    <li>
        <input name="protected" type="radio" value="N" <?php if(!$protected) echo "checked"; ?> /> 允许所有人查看并显示在叽歪广场
    </li>
    <li>
        <input name="protected" type="radio" value="Y" <?php if($protected) echo "checked"; ?>/> 只允许我的好友查看
    </li>
</ul>

    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-sure.gif'); ?>" alt="确定" /></a>
    </div>            
</form>

</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
