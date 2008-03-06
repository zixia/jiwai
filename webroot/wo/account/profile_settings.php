<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

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
		// PHP upload error, except NO FILE(that mean user want to delete).
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

?>
<html>


<head>

<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 


$asset_url_moorainbow_img_path	= JWTemplate::GetAssetUrl('/lib/mooRainbow/images/', false);
$asset_url_moorainbow_js		= JWTemplate::GetAssetUrl('/lib/mooRainbow/mooRainbow.js');
$asset_url_moorainbow_css		= JWTemplate::GetAssetUrl('/lib/mooRainbow/mooRainbow.css');

echo <<<_HTML_
<link href="$asset_url_moorainbow_css" media="screen, projection" rel="Stylesheet" type="text/css" />
<script src="$asset_url_moorainbow_js" type="text/javascript"></script>

_HTML_;

$color_ids = array 
( 
	 'user_profile_background_color'
	/*,'user_profile_text_color'
	,'user_profile_name_color'
	,'user_profile_link_color'
	,'user_profile_sidebar_fill_color'
	,'user_profile_sidebar_border_color'*/
);

echo <<<_HTML_
<script type="text/javascript">
//<![CDATA[

window.addEvent('domready', function() 
{

_HTML_;

foreach ( $color_ids as $color_id )
{
	$k = preg_replace('/^user_/','',$color_id);

	echo <<<_HTML_
try {

	var default_bg_color = new Color('$user[$k]');
	var default_fg_color = default_bg_color.invert();

	$('$color_id').setStyle('background-color'	, default_bg_color);
	$('$color_id').setStyle('color'				, default_fg_color);


	var ${color_id}_r = new MooRainbow('$color_id', 
	{
		 id: '${color_id}_moo_id'
		//,startColor: [58, 142, 246]
		,startColor: default_bg_color
		,imgPath: '$asset_url_moorainbow_img_path'
		,wheel: true
		,onChange: function(color) 
		{
			$('$color_id').setStyle('background-color'	, color.hex);
			$('$color_id').setStyle('color'				, (new Color(color.hex)).invert());
			$('$color_id'+'_value').value = color.hex;
			$('$color_id'+'_val').innerHTML = color.hex;
		}
/*
		,onComplete: function(color)
		{
			$('$color_id').setStyle('background-color', color.hex);
			$('$color_id').value = color.hex;
		}
*/
	});
} catch (e) {
}

_HTML_;
}

echo <<<_HTML_

});

//]]>
</script>

_HTML_;

?>

</head>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings" class="now">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<?php
$ui->GetUseBackgroundImage($picture_id);
$picture_name = '无';
if ( $picture_id )
{
	$pic_db_row = JWPicture::GetDbRowById($picture_id);
	if ( false==empty($pic_db_row) )
	{
		$picture_name = $pic_db_row['fileName'] . '.' . $pic_db_row['fileExt'];
	}
}
?>
<div class="rightdiv">
<div class="lookfriend">
<form id="f" action="" method="post" name="f" enctype="multipart/form-data">
<input type="hidden" name="commit_x" value="1"/>
<input type="hidden" id="user_profile_background_color_value" name="user[profile_background_color]" value="#<?php echo $user['profile_background_color']?>" />
       <div class="protection">
	    <p><span class="black15bold">背景颜色:</span><input id="user_profile_background_color" size="30" type="text" class="personalized_bc"/>
		&nbsp;&nbsp;<span class="black12" id="user_profile_background_color_val">#<?php echo $user['profile_background_color'];?></span>
	    <p><span class="black15bold">背景图片:</span><input class="checkbox" id="user_profile_use_background_image" name="user[profile_use_background_image]" <?php if($picture_id) echo "checked";?> type="checkbox" value="checked" />
					<input style="display:inline;" id="user_profile_background_image" name="profile_background_image" size="30" type="file" class="inputStyle2"/>
					<br />
		<div class="personalized">
		<p class=" personalizedText">最大可以上传 2M 大小的图片</p>
        <p><label for="user_profile_background_tile1"><input type="radio" id="user_profile_background_tile1" name="user[profile_background_tile]" value="1" <?php if ( $user['profile_background_tile'] ) echo 'checked="checked" ';?>/><span class="pad3">平铺&nbsp;&nbsp;</span></label>
        <label for="user_profile_background_tile2"><input type="radio" id="user_profile_background_tile2" name="user[profile_background_tile]" value="0" <?php if ( !$user['profile_background_tile'] ) echo
		     'checked="checked" ';?>/><span class="pad3">不平铺</span></label></p>
	    <p>当前背景图片：<?php echo $picture_name?></p>
	    <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
	   <p><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></p>
	    <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
	<p><a class="orange12" href="/wo/account/restore_profile" onclick="return confirm('请确认你希望恢复叽歪网缺省设计方案？');">恢复叽歪网缺省配色方案</a></p>
	   </div>
	   </div>
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<?php JWTemplate::container_ending(); ?>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
