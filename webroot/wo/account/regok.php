<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info		= JWUser::GetCurrentUserInfo();
$has_photo		= !empty($user_info['idPicture']);

if ( $has_photo ){
    // we have photo
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
}else{
    // we have no photo
    $photo_url = JWTemplate::GetAssetUrl('/images/org-nobody-96-96.gif');
}

if ( isset($_POST['upload'] ) ) {
	$file_info = @$_FILES['profile_image'];
	if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) ) 
	{
		$user_named_file = '/tmp/' . $file_info['name'];
		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) ) {
			$idPicture = JWPicture::SaveUserIcon($user_info['id'], $user_named_file);
			if ( $idPicture ) {
				preg_match('/([^\/]+)$/',$user_named_file,$matches);
				JWUser::SetIcon($user_info['id'], $idPicture);
				$notice_html = "<li>头像修改成功！</li>";
				JWSession::SetInfo('notice', $notice_html);
				JWTemplate::RedirectToUrl("/wo/account/regook");
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
	JWTemplate::RedirectToUrl("/wo/account/regok");
}

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '恭喜你注册成功' );
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_tips();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_account_regok();?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_regok();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
