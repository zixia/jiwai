<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

if ( $_POST ) {
	JWUtility::CheckCrumb();
	$user_id = JWLogin::GetCurrentUserId();
	if ( !JWUser::IsAdmin($user_id) ) {
		JWSession::SetInfo('notice', "再见，{$user_info['nameScreen']}，欢迎下次来玩！");
		JWLogin::Logout();
		JWUser::Destroy($user_id);
	}
	JWTemplate::RedirectToUrl('/');
}

$element = JWElement::Instance();
$param_main = array();

$param_tab = array( 'tabtitle' => '删除账户' );
$param_side = array( 'sindex' => 'account' );
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
		<?php $element->block_account_delete($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
