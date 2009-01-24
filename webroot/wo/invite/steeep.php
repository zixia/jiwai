<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$not_reg = null;
extract($_POST, EXTR_IF_EXISTS);

$current_user_id = JWLogin::GetCurrentUserId();
$current_user_info = JWUser::GetCurrentUserInfo();

if (!empty($not_reg)) {
	if ( JWSns::EmailInvite($not_reg, $current_user_info)) {
		JWSession::SetInfo('notice', '已经帮你向你的朋友们发送了邮件邀请。');
	} else {
		JWSession::SetInfo('notice', '对不起，暂时无法用邮件邀请你的朋友。');
	}
	JWTemplate::RedirectToUrl('/wo/invite/steeep');
}else if($_POST){
	JWSession::SetInfo('notice', '对不起，你没有选中任何待邀请的朋友。');
	JWTemplate::RedirectToUrl('/wo/invite/steeep');
}

//elements begin;
$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '邀请朋友操作成功' );
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
		<?php $element->block_invite_steeep();?>
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
<?php $element->html_footer();?>
