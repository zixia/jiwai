<?php
require_once( '../../jiwai.inc.php');
JWLogin::MustLogined(false);

$element = JWElement::Instance();
$param_tab = array( 'now' => 'wo_following' );
$param_notice = array(
	'forcenotice' => '<li>叽歪网正在改版测试中，有任何意见或建议欢迎到<a href="http://jiwai.de/t/改版反馈/">这里</a>反馈，我们将会持续的努力的改进叽歪。如果不喜欢叽歪的配色可以在<a href="http://jiwai.de/wo/design/">这里</a>进行设置。</li><li>或者<a href="http://alpha.jiwai.de/">去旧版叽歪</a>。</li><li>你还可以在<a href="http://lab.jiwai.de/googlemap/" target="_blank">这里</a>感受上帝之眼。</li>',
);
?>
<?php $element->html_header();?>
<?php $element->common_header_wo();?>

<div id="container">
<?php $element->wide_notice($param_notice);?>
<div id="lefter">

	<div class="mar_b20">
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_headline_wo();?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
	
	<div>
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_searchstatus();?>
			<?php $element->block_tab($param_tab);?>
			<?php $element->block_statuses_wo_with_friends();?>
			<?php $element->block_rsslink();?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>

</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_wo_request_in();?>
		<?php $element->side_wo_hi();?>
		<?php $element->side_announcement();?>
		<div class="line mar_b8"></div>
		<?php $element->side_recent_vistor();?>
		<?php $element->side_whom_me_follow(array('url'=>'wo'));?>
		<?php $element->side_searchuser();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
