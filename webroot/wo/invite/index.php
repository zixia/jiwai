<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);
list($_, $oblockid, $cid) = @explode('/', @$_REQUEST['pathParam']);

$cids = array('','twitter','fanfou','douban');
$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '寻找在叽歪的好友' );
$param_main = array(
	'cid' => $cids[abs(intval($cid))],
);
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_invite_index($param_main);?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_invite_index();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer(array('oblockid'=>$oblockid));?>
