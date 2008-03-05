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
$protected	= $user_info['protected'] == 'Y';

 if ( $has_photo ){
    // we have photo
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
}else{
    // we have no photo
    $photo_url = JWTemplate::GetAssetUrl('/images/org-nobody-96-96.gif');
}

//echo "<pre>"; die(var_dump($user_info));
//var_dump($file_info);
//var_dump($_POST);
if ( isset($_POST['save_x'] ) ) 
{
    if(!empty($_POST['skip']))
	JWTemplate::RedirectToUrl("/wo/account/regok2");

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
				JWTemplate::RedirectToUrl("/wo/account/regok2");
			} else {
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = <<<_HTML_
<li>上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
				JWSession::SetInfo('error',$error_html);
				JWTemplate::RedirectToUrl("/wo/account/regok");
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
		JWTemplate::RedirectToUrl("/wo/account/regok");
				break;
			default:
				throw new JWException("upload error $file_info[error]");
				break;
		}
	}

		JWTemplate::RedirectToUrl("/wo/account/regok2");
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="account" id="regok">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTips(); ?>

<div id="container">
    <p class="top">恭喜你注册成功</p>
    <div id="wtMainBlock">
	<div class="leftdiv">
            <span class="bluebold16">你知道吗？绑定手机、MSN、QQ或Gtalk等后，可以方便地修改你的用户名和密码！</span>
            <p>发送<span class="orange12">gm+空格+想要用户名</span>，到相应的短信号码或者机器人上来设置用户名<br />例如：gm 阿朱</p>
            <p>发送<span class="orange12">mima+空格+密码</span>，来设置密码<br />例如：mima abc123 </p>
	</div><!-- leftdiv -->
	<div class="rightdiv">
	    <div class="login">

<p>你好，<?php echo htmlSpecialChars($user_info['nameScreen']); ?>，要<a href="/wo/">马上开始叽歪</a>吗？你可以先：</p>

<form id="f" action="/wo/account/regok" method="POST" enctype="multipart/form-data">
<input type="hidden" name="save_x" value="1"/>
	<div class="accountBox">
     <div class="accountHead"><a href="/<?php echo $user_info['nameUrl'];?>/"><img width="96" height="96" title="<?php echo $user_info['nameFull'];?>" src="<?php echo $photo_url;?>"/></a></div>
	  <div class="accountCont">
	   <p class="black15bold">上传新头像</p>
	   <p class="gray12">支持.jpg .gif .png的图片,最大可以上传 2M 大小的图片</p>
       <p>
	 <input name="profile_image" type="file" class="inputStyle2" style=" margin:0;"/>
       </p>
       <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
	   <p><input type="submit" id="save" name="save" class="submitbutton" value="上传" />&nbsp;&nbsp;
	   <input type="submit" id="skip" name="skip" class="closebutton" value="跳过" />
	   </p>
	   </div><!-- accountCont -->
	   </div><!-- accountBox -->
		</form>
		<div style="overflow: hidden; clear: both; height: 70px; line-height: 1px; font-size: 1px;"></div>
	    </div><!-- login end -->

	</div><!-- rightdiv end -->
    </div><!-- #wtMainBlock end -->
    <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container end -->

<?php JWTemplate::footer() ?>

</body>
</html>
