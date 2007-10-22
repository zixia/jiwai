<?php

$user = array();
extract($_REQUEST, EXTR_IF_EXISTS);


require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

//var_dump($_REQUEST);

$user_info		= JWUser::GetCurrentUserInfo();


$ui = new JWDesign($user_info['idUser']);


//var_dump($file_info);
if ( $_SERVER["REQUEST_METHOD"]=='POST' )
{
	
//echo "<pre>"; die(var_dump($_REQUEST));
//die(var_dump($user));
	$file_info = @$_FILES['profile_background_image'];
//die(var_dump($file_info));
	
	if ( ! $user['profile_use_background_image'] 
		&& !isset($file_info) )
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
			$idPicture	= JWPicture::SaveBg($user_info['id'], $user_named_file);
			if ( $idPicture )
			{
				$user['profile_use_background_image'] = $idPicture;
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = <<<_HTML_
<li>上传图片失败，请检查图片图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
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

//die(var_dump($user));
	$ui->SetBackgroundColor	($user['profile_background_color']);
	$ui->SetUseBackgroundImage($user['profile_use_background_image']);
	$ui->SetBackgroundTile	($user['profile_background_tile']);
/*	$ui->SetTextColor		($user['profile_text_color']);
	$ui->SetNameColor		($user['profile_name_color']);
	$ui->SetLinkColor		($user['profile_link_color']);
	$ui->SetSidebarFillColor($user['profile_sidebar_fill_color']);
	$ui->SetSidebarBorderColor($user['profile_sidebar_border_color']);
*/
//die(var_dump($ui));
	$ui->Save();

	header('Location: ' . $_SERVER['SCRIPT_URI']);
	exit(0);
}
else
{
//die(var_dump($ui));
	$ui->GetBackgroundColor	($user['profile_background_color']);
	$ui->GetUseBackgroundImage($user['profile_use_background_image']);
	$ui->GetBackgroundTile	($user['profile_background_tile']);
	$ui->GetTextColor		($user['profile_text_color']);
	$ui->GetNameColor		($user['profile_name_color']);
	$ui->GetLinkColor		($user['profile_link_color']);
	$ui->GetSidebarFillColor($user['profile_sidebar_fill_color']);
	$ui->GetSidebarBorderColor($user['profile_sidebar_border_color']);
}


//die(var_dump($user));

?>
<html>


<head>

<?php 
JWTemplate::html_head();


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
	,'user_profile_text_color'
	,'user_profile_name_color'
	,'user_profile_link_color'
	,'user_profile_sidebar_fill_color'
	,'user_profile_sidebar_border_color'
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
			$('$color_id').value = color.hex;
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

<div id="container" class="subpage">
<?php JWTemplate::SettingTab('/wo/account/profile_settings'); ?>

<div class="tabbody">
<h2>设计你自己de叽歪档案</h2>
<div style="width:500px; margin:30px auto; font-size:14px;">

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

?>

<!-- p>
	下面是你当前叽歪档案的设计方案，<br />
	你可以随时修改、预览、保存设计方案，也可以非常容易的将其缺省值。
</p -->

<form id="f" action="/wo/account/profile_settings" enctype="multipart/form-data" method="post">
	<fieldset>
		<table width="100%" cellspacing="3">
			<tr>
				<td>背景颜色：</td>
				<td width="350"><input id="user_profile_background_color" name="user[profile_background_color]" size="30" type="text" value="<?php echo $user['profile_background_color']?>" /></td>
			    <td class="note"></td>
			</tr>
			<tr>
				<td>背景图片：</td>
				<td>
					<input style="display:inline;border:0px;width:20px;" <?php 
						$picture_name 	= '无';
						$picture_id		= 0;
						if ( $user['profile_use_background_image'] )
						{
							$pic_db_row = JWPicture::GetDbRowById($user['profile_use_background_image']);
							
							if ( !empty($pic_db_row) )
							{
								echo ' checked="checked" ';
								$picture_name 	= $pic_db_row['fileName'] . '.' . $pic_db_row['fileExt'];
								$picture_id		= $pic_db_row['idPicture'];
							}
						}
					?> id="user_profile_use_background_image" name="user[profile_use_background_image]" type="checkbox" value="<?php echo $picture_id?>" />
					<input style="display:inline;border:0px;" id="user_profile_background_image" name="profile_background_image" size="30" type="file" />
					<br />
			    	<input id="user_profile_background_tile" style="display:inline;border:0;width:20px;"<?php
						if ( $user['profile_background_tile'] ) echo 'checked="checked" ';
					?> name="user[profile_background_tile]" type="checkbox" value="1" />
					<label for="user_profile_background_tile">平铺</label>
                    <span class="note" style="padding-left:5px;">最大可以上传 2M 大小的图片</span>
					<br />
					当前背景图片：<small>(<?php echo $picture_name?>)</small>
				</td>
			    <td>
				</td>
			</tr>
		</table>
		<input id="siv" name="siv" type="hidden" value="4fb7e754a2db9aa5b100da3b9c9e6de6" />
  
	</fieldset>
    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
    </div>
	<a href="/wo/account/restore_profile" onclick="return confirm('请确认你希望恢复叽歪de缺省设计方案？');">恢复叽歪de缺省配色方案</a>

</form>

</div>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
