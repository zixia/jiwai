<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

$design = new JWDesign($current_user_id);
if ( $_POST )
{
	$ui = $_POST['ui'];
	$file_info = @$_FILES['profile_background_image'];
	if ( false==isset($ui['profile_use_background_image']) 
		&& false==isset($file_info) )
	{
		// 不使用背景图片
		$ui['profile_use_background_image'] = null;
	}
	else if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
			)
	{
			
		$ui_named_file = '/tmp/' . $file_info['name'];

		if ( move_uploaded_file($file_info['tmp_name'], $ui_named_file) )
		{
			$picture_id = JWPicture::SaveBg($current_user_id, $ui_named_file);
			if ( $picture_id )
			{
				$ui['profile_use_background_image'] = $picture_id;
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');
				$error_html = '上传图片失败，请检查图片图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="'.$contact_url.'">联系我们</a>';
				JWSession::SetInfo('error',$error_html);
			}
			@unlink ( $ui_named_file );
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

	$ui['profile_design_choice'] = array($ui['profile_design_choice_whole'],$ui['profile_design_choice_side']);
	$design->SetBackgroundColor	($ui['profile_background_color']);
	$design->SetUseBackgroundImage	($ui['profile_use_background_image']);
	$design->SetBackgroundTile	($ui['profile_background_tile']);
	$design->SetDesignChoice	($ui['profile_design_choice']);

	$design->Save();
	JWTemplate::RedirectToUrl($_SERVER['REQUEST_URI']);
}
else
{
	$ui = array();
	$design->GetBackgroundColor	($ui['profile_background_color']);
	$design->GetUseBackgroundImage	($ui['profile_use_background_image']);
	$design->GetBackgroundTile	($ui['profile_background_tile']);
	$design->GetTextColor		($ui['profile_text_color']);
	$design->GetNameColor		($ui['profile_name_color']);
	$design->GetLinkColor		($ui['profile_link_color']);
	$design->GetSidebarFillColor	($ui['profile_sidebar_fill_color']);
	$design->GetSidebarBorderColor	($ui['profile_sidebar_border_color']);
	$design->GetDesignChoice	($ui['profile_design_choice']);
}

$picture_name = '无';
$design->GetUseBackgroundImage($picture_id);
if ( $picture_id ) {
	$picture_row = JWPicture::GetDbRowById($picture_id);
	$picture_name = ($picture_row) ? "{$picture_row['fileName']}.{$picture_row['fileExt']}" : '无';
}

list($design_choice_whole,$design_choice_side) = $ui['profile_design_choice'];
$design_choice_whole = $design_choice_whole
	? JWTemplate::GetAssetUrl("/css/{$design_choice_whole}.css")
	: '#';

$element = JWElement::Instance();
$param_head = array( 'design' => $design, );
$param_tab = array( 'tabtitle' => '个性化界面',);
$param_side = array( 'sindex' => 'design' );
$param_main = array(
	'picture_id' => $picture_id,
	'picture_name' => $picture_name,
	'ui' => $ui,
);
?>
<?php $element->html_header($param_head);?>
<!-- desin -->
<link href="<?php echo JWTemplate::GetAssetUrl('/lib/mooRainbow/mooRainbow.css');?>" media="screen, projection" rel="Stylesheet" type="text/css" />
<script src="<?php echo JWTemplate::GetAssetUrl('/lib/mooRainbow/mooRainbow.js');?>" type="text/javascript"></script>
<link id="set_style" href="#" media="screen, projection" rel="Stylesheet" type="text/css" />
<!-- design end -->
<?php $element->common_header_wo();?>
<div id="container">

<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_design($param_main);?>
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
