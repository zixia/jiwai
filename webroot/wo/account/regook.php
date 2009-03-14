<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$element = JWElement::Instance();

$featured_ids = JWUser::GetFeaturedUserIds( 10, 'commender' );
$featured_users = JWDB_Cache_User::GetDbRowsByIds($featured_ids);

$picture_ids = JWFunction::GetColArrayFromRows($featured_users, 'idPicture');
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);

$param_regook = array(
	'featured_users' => $featured_users,
	'picture_urls' => $picture_urls,
);
$param_tab = array( 'tabtitle' => '恭喜你注册成功' );
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_tips();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_account_regook($param_regook);?>
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
