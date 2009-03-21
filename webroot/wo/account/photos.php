<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$outInfo = $user_info;
$has_photo = !empty($user_info['idPicture']);

if ( !empty($_FILES ) )
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
			$picture_id = JWPicture::SaveUserIcon($user_info['id'], $user_named_file);
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

$element = JWElement::Instance();
$param_tab = array( 'now' => 'account_photos' );
$param_side = array( 'sindex' => 'account' );
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_account_photos();?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
