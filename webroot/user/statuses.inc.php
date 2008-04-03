<?php
function user_status($page_user_id, $status_id)
{
	JWTemplate::html_doctype();

	$status_rows	= JWStatus::GetDbRowsByIds(array($status_id));
	$status_info	= @$status_rows[$status_id];

	$page_user_info	= JWUser::GetUserInfo($page_user_id);
	if ( $status_info['idUser']!==$page_user_id && false == ( @$status_info['idConference']!=null 
						&& $status_info['idConference'] == $page_user_info['idConference'] 
					) 
		)
	{
		JWTemplate::RedirectToUserPage( $page_user_info['nameUrl'] );
		exit(0);
	}

	$current_user_id = JWLogin::GetCurrentUserId();

	$formated_status = JWStatus::FormatStatus($status_info,false);

	$petty_device = JWDevice::GetNameFromType( $status_info['device'], $status_info['idPartner'] );

	$protected = JWSns::IsProtected( $page_user_info, $current_user_id );

?>
<html>

<head>

<style type="text/css">
h2.thumb, h2.thumb a {
color:#000000;
}
#content div.desc{
background: transparent url()
}
</style>


<?php 
$head_options = array ( 'ui_user_id'=>$page_user_id );
JWTemplate::html_head($head_options) ;
?>
</head>

<body class="status" id="show">

<?php //JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>


<div id="container">
<div id="content">
<div id="wrapper">

<?php JWTemplate::StatusHead( $page_user_info, $status_info, $options=array('isMyPages'=>false) ); ?>	

</div><!-- wrapper -->
</div><!-- content -->

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>

</body>
</html>

<?php } ?>
