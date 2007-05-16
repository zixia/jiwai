<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
?>
<html>

<?php JWTemplate::html_head() ?>


<body class="status" id="public_timeline">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">


<?php JWTemplate::ShowActionResultTips(); ?>


			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'最新动态 - 大家在做什么？' 
									, 'title2'	=>	'你想叽歪呀，你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢？：-）'
							) )
?>

<?php 
$status_data 	= JWStatus::GetStatusIdsFromPublic();
$status_rows	= JWStatus::GetStatusRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows) 
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php 
$arr_menu = array(	array ('head'			, array('JiWai.de <strong>叽歪广场</strong>'))
					, array ('featured'			, null)
				);

if ( ! JWLogin::IsLogined() )
	array_push ($arr_menu, array('register', null));

JWTemplate::sidebar($arr_menu, null);
?>
			
		
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
