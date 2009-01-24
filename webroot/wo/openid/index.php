<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$openid = JWOpenID::GetDbRowByUserId($user_info['idUser']);
$urlOpenid= empty($openid) ? 'http://'.$_SERVER['HTTP_HOST'].'/'.$user_info['nameUrl'] : $openid['urlOpenid'];

if ( isset($_REQUEST['user']) )
{
	// 用户输入了自己的  openid，需要去验证
	$openid_url = $_REQUEST['user']['openid'];
	if ( JWOpenID::IsPossibleOpenID($openid_url) )
	{
		JWOpenID::AuthRedirect($openid_url);
	} else {
		$error_html = "你输入的 OpenID：{$openid_url} 有误，请查证后重试。";
		JWSession::SetInfo('notice', $error_html);
		JWTemplate::RedirectToUrl('/wo/openid/');
	}
}

$trusted_site_ids = JWOpenID_TrustSite::GetIdsByUserId($user_info['id']);
$trusted_sites = JWOpenID_TrustSite::GetDbRowsByIds($trusted_site_ids);

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => 'OpenID设置' );
$param_side = array( 'sindex' => 'openid' );
$param_main = array(
	'trusted_sites' => $trusted_sites,
	'urlOpenid' => $urlOpenid,
);
?>

<?php $element->html_header();?>
<?php $element->common_header_wo();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_openid($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
