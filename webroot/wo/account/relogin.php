<?php
require_once('../../../jiwai.inc.php');
$element = JWElement::Instance();
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<div class="pagetitle">
			<h1>随时随地随时随地</h1>
		</div>
		<div class="tag">
			<ul>
				<h2><b>登录到叽歪</b></h2>
			</ul>
		</div>
		<?php $element->block_login();?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_relogin();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
