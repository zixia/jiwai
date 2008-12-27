<?php
$element = JWElement::Instance();
$user = JWUser::GetUserInfo($g_page_user_id);
$param_tab = array( 'now' => 'user_following' );
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_user();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_statuses_with_friends();?>
			<?php $element->block_rsslink();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div>
<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_profile_wo();?>
		<?php $element->side_recent_vistor();?>
		<?php $element->side_whom_me_follow();?>
		<?php $element->side_block_user();?>
		<div class="gra_input"><input type="text" value="QQ  MSN  Emai l id..." onFocus="clearValue(this)" onBlur="searchValue(this,'QQ  MSN  Emai l id...')" /> <input type="button" value="谁在叽歪" class="def_btn" /></div>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
