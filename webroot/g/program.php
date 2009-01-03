<?php
require_once('../../jiwai.inc.php');
list($_, $oblockid) = @explode('/', @$_REQUEST['pathParam']);
$oblockid = abs(intval($oblockid));

$program_ids = array(162559, 162570);
$program_users = JWDB_Cache_User::GetDbRowsByIds($program_ids);
$picture_ids = JWUtility::GetColumn($program_users, 'idPicture');
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);

$program_users = array(
	0 => range(51685,51700),
	1 => array_merge(range(76800,76827), array(56214)),
	2 => range(52058,52067),
	3 => range(51948,51958),
	4 => range(52090,52096),
);

$block_tab = array(
	0 => array('中央电视台', '/g/program'),
	1 => array('全国卫视', '/g/program/1'),
	2 => array('北京台', '/g/program/2'),
	3 => array('上海台', '/g/program/3'),
	4 => array('广东台', '/g/program/4'),
);

$element = JWElement::Instance();
$param_tab = array(
	'tab' => $block_tab,
	'now' => 'program_' . $oblockid,
);

$param_main = array(
		'user_ids' => $program_users[$oblockid],
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
				<h1>叽歪电视节目预告</h1>
			</div>
		</div>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<div class="block">
				<?php $element->block_statuses_muser($param_main);?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter_g">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_g_program();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
