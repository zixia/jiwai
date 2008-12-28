<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user = array();
extract($_REQUEST, EXTR_IF_EXISTS);
if (false===isset($user['profile_background_tile']) )
	$user['profile_background_tile'] = 0;
if (false===isset($user['profile_background_image']) )
	$user['profile_background_image'] = null;
if (false===isset($user['profile_use_background_image']) )
	$user['profile_use_background_image'] = null;

$user_info = JWUser::GetCurrentUserInfo();

$ui = new JWDesign($user_info['idUser']);

if ( $_SERVER["REQUEST_METHOD"]=='POST' )
{
	$file_info = @$_FILES['profile_background_image'];
	
	if ( false==isset($user['profile_use_background_image']) 
		&& false==isset($file_info) )
	{
		// 不使用背景图片
		$user['profile_use_background_image'] = null;
	}
	else if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
			)
	{
			
		$user_named_file = '/tmp/' . $file_info['name'];

		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) )
		{
			$picture_id = JWPicture::SaveBg($user_info['id'], $user_named_file);
			if ( $picture_id )
			{
				$user['profile_use_background_image'] = $picture_id;
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = '上传图片失败，请检查图片图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="'.$contact_url.'">联系我们</a>';
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
		switch ( $file_info['error'] )
		{
			case UPLOAD_ERR_INI_SIZE:
				JWSession::SetInfo('notice','头像文件尺寸太大了，请将图片缩小分辨率后重新上载。');
				break;
			default:
				throw new JWException("upload error $file_info[error]");
				break;
		}
	}

/*
  ["profile_background_color"]=>
  ["profile_use_background_image"]=>
  ["profile_background_tile"]=>
  ["profile_text_color"]=>
  ["profile_name_color"]=>
  ["profile_link_color"]=>
  ["profile_sidebar_fill_color"]=>
  ["profile_sidebar_border_color"]=>
*/

	$ui->SetBackgroundColor	($user['profile_background_color']);
	$ui->SetUseBackgroundImage($user['profile_use_background_image']);
	$ui->SetBackgroundTile	($user['profile_background_tile']);
/*	$ui->SetTextColor		($user['profile_text_color']);
	$ui->SetNameColor		($user['profile_name_color']);
	$ui->SetLinkColor		($user['profile_link_color']);
	$ui->SetSidebarFillColor($user['profile_sidebar_fill_color']);
	$ui->SetSidebarBorderColor($user['profile_sidebar_border_color']);
*/
	$ui->Save();

	JWTemplate::RedirectToUrl($_SERVER['REQUEST_URI']);
}
else
{
	$ui->GetBackgroundColor	($user['profile_background_color']);
	$ui->GetUseBackgroundImage($user['profile_use_background_image']);
	$ui->GetBackgroundTile	($user['profile_background_tile']);
	$ui->GetTextColor		($user['profile_text_color']);
	$ui->GetNameColor		($user['profile_name_color']);
	$ui->GetLinkColor		($user['profile_link_color']);
	$ui->GetSidebarFillColor($user['profile_sidebar_fill_color']);
	$ui->GetSidebarBorderColor($user['profile_sidebar_border_color']);
}

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '个性化界面',);
$param_side = array( 'sindex' => 'design' );
?>

<?php $element->html_header();?>
<?php $element->common_header_wo();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_design($param_main);?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
