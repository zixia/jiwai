<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

$dm_user_id = null;
if ( preg_match('/^\/(\d+)$/',@$_REQUEST['pathParam'] ,$matches) )
{
	$dm_user_id = intval($matches[1]);
} else {
	$dm_user_id = abs(intval(@$_POST['dm_user_id']));
}

$dm_message_id = abs(intval(@$_POST['dm_message_id']));


if ( ! $dm_user_id )
	JWTemplate::RedirectToUrl('/wo/direct_messages/');

$user = JWUser::GetUserInfo( $dm_user_id );
if ( empty($user) )
	JWTemplate::RedirectTo404NotFound();

$element = JWElement::Instance();
$param_dm = array( 'to' => $user );


// create new direct_messages
if ( isset($_REQUEST['jw_status']) ) {
	$message = $_REQUEST['jw_status'];
	$message = trim($message);
	if ( false==empty($message) ) {
		$dm_user = JWUser::GetUserInfo( $dm_user_id );
                JWSns::ExecWeb($current_user_id, "D {$dm_user['nameScreen']} {$message}");
	} else {
		JWSession::SetInfo('哎呀！请不要发送空悄悄话！');
		JWTemplate::RedirectBackToLastUrl('/');
	}
	JWTemplate::RedirectToUrl('/wo/direct_messages/sent');
}
?>

<?php $element->html_header();?>
<?php $element->common_header_wo();?>
<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_dm($param_dm);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div>
<div id="righter">
        <div class="a"></div><div class="b"></div><div class="c"></div><div class="
d"></div>
        <div id="rightBar" class="f" >
                <?php $element->side_wo_request_in();?>
                <?php $element->side_wo_hi();?>
                <?php $element->side_announcement();?>
                <div class="line mar_b8"></div>
                <?php $element->side_recent_vistor();?>
                <?php $element->side_whom_me_follow(array('url'=>'wo'));?>
                <?php $element->side_block_user();?>
                <?php $element->side_searchuser();?>
        </div>
        <div class="d"></div><div class="c"></div><div class="b"></div><div class="
a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
