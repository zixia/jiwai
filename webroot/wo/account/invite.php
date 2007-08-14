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
if ( isset($_POST['save_x'] ) ) {

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
	            header('Location: /wo/invitations/invite');
                exit(0);
			} else {
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = <<<_HTML_
<li>上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
				JWSession::SetInfo('error',$error_html);
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
				break;
			default:
				throw new JWException("upload error $file_info[error]");
				break;
		}
	}

	header('Location: /wo/invitations/invite');
	exit(0);
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="invite">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>


<div id="container">

<h2>邀请朋友一起来叽歪</h2>
<p>如果暂时不想邀请朋友，可以点击进入<a href="/wo/">我的首页</a></p>

<p class="subtab"><a href="#" class="now">通过MSN邀请</a><a href="invitebymail.html">通过Email邀请</a><a href="invitebymsg.html">通过短信邀请</a></p>

<form>

<div class="tabbody">
    <div>把下面的网址通过MSN发送给朋友</div>
    <div>
            <input type="text" size="50" value="http://JiWai.de/wo/invitations/i/YAOQINGDAIMA"/>
    </div>
    <div style="margin-bottom:20px;">朋友注册后你们自动成为叽歪上的好友。</div>
</div>
<div class="but">
    <input name="save" type="image" src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-sure.gif'); ?>" alt="确定" width="112" height="33" border="0" />　　<a href="/wo/"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-skip.gif'); ?>" alt="跳过" width="112" height="33" border="0" /></a>
</div>

</form>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>          
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
