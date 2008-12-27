<?php
require_once('../../jiwai.inc.php');

$astro_ids = range(162559, 162570);

/*
$astro_users = JWDB_Cache_User::GetDbRowsByIds($astro_ids);
$picture_ids = JWUtility::GetColumn($astro_users, 'idPicture');
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);
*/

$element = JWElement::Instance();
$param_main = array(
//		'astro_users' => $astro_users,
//		'picture_urls' => $picture_urls,
		'user_ids' => $astro_ids,
		);
?>

<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<?php $element->wide_notice();?>
<div id="lefter_g">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<div class="f">
			<div class="pagetitle">
				<h1>叽歪星座运程</h1>
			</div>
			<div class="block">
				<?php $element->block_statuses_muser($param_main);?>
			</div>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter_g">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_g_astro();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
