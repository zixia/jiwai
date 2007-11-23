<?php
function user_status($idPageUser, $idStatus)
{
	JWTemplate::html_doctype();

	$status_rows	= JWStatus::GetDbRowsByIds(array($idStatus));
	$status_info	= @$status_rows[$idStatus];

	$page_user_info	= JWUser::GetUserInfo($idPageUser);
	if ( $status_info['idUser']!==$idPageUser && false == ( @$status_info['idConference']!=null 
						&& $status_info['idConference'] == $page_user_info['idConference'] 
					) 
		)
	{
		JWTemplate::RedirectToUserPage( $page_user_info['nameUrl'] );
		exit(0);
	}

	$logined_user_info	= JWUser::GetCurrentUserInfo();

	$formated_status 	= JWStatus::FormatStatus($status_info,false);

	$pettyDevice = JWDevice::GetNameFromType( $status_info['device'], $status_info['idPartner'] );

	$protected = false;
	if ( JWUser::IsProtected($idPageUser) )
	{
		$protected = true;
		if ( ! empty($logined_user_info) )
		{
			if ( JWFollower::IsFollower($logined_user_info['idUser'], $idPageUser) || $logined_user_info['idUser']==$idPageUser )
				$protected = false;
		}
	}

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
$head_options = array ( 'ui_user_id'=>$idPageUser );
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
<?php 

	JWTemplate::StatusHead($page_user_info['id'], $page_user_info, $status_info, $options=null, false==$protected);
?>	
</div><!-- wrapper -->
</div><!-- content -->

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>

</body>
</html>

<?php } ?>
