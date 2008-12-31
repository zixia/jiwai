<?php
$status_info = JWDB_Cache_Status::GetDbRowById($status_id);
$page_user_info	= JWUser::GetUserInfo($page_user_id);

if ( 'N' == $page_user_info['protected'] ) {
	$title = mb_strimwidth($status_info['status'], 0, 40,'...');
	$param_head = array(
		'title' => "{$page_user_info['nameScreen']} : {$title}",
	);
}

if ( $status_info['idUser'] !== $page_user_id 
	&& false == (@$status_info['idConference']!=null 
		&& $status_info['idConference'] == $page_user_info['idConference'])
   ) {
	JWTemplate::RedirectToUserPage( $page_user_info['nameUrl'] );
	exit(0);
}

$element = JWElement::Instance();
$param_main = array(
	'thread_id' => $status_id,
	'noupdater' => true,
);
?>
<?php $element->html_header($param_head);?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div class="wht">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<?php $element->block_headline_thread($param_main);?>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>
<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
