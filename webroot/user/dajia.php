<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../jiwai.inc.php');
JWDebug::init();
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


			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'大家最新的叽叽歪歪' 
									, 'title2'	=>	'你想叽歪呀，你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢？：-）'
							) )
?>

<?php 
$aStatusList = JWStatus::get_status_list_timeline();
JWTemplate::timeline($aStatusList) 
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php 
$arr_menu = array(	'timeline_notice'
					, 'featured'
				);
if ( ! JWUser::IsLogined() )
	array_push ($arr_menu, 'register');

JWTemplate::sidebar($arr_menu);
?>
			
		
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
