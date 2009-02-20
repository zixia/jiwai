<?php
require_once( '../../jiwai.inc.php' );
$element = JWElement::Instance();

$words = JWTrackUser::GetWordListByIdUser( $g_page_user['idUser'], false );

$param_main = array(
	'words' => $words,
	'title' => "{$g_page_user['nameScreen']}追踪的" .count($words) ."个词汇",
);

$param_tab = array(
	'now' => 'ut_fkey',
	'tab' => array(
		'owner' =>  array('此人话题', "/{$g_page_user['nameUrl']}/t/"),
		'ftag' => array('此人关注', "/{$g_page_user['nameUrl']}/tfollowings/"),
		'fkey' => array('追踪词汇',	"/{$g_page_user['nameUrl']}/kfollowings/"),
	),
);
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
		<?php $element->block_key_user($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div>
<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_key_user($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
