<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../jiwai.inc.php');

JWUser::MustLogined();

$idUser = JWUser::GetCurrentUserInfo('id');

$debug = JWDebug::instance();
$debug->init();
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">
<?php 
$now_str = strftime("%Y/%m/%d") ;
echo <<<_HTML_
	<div id="flaginfo">$now_str</div>
_HTML_;
?>
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">

<?php JWTemplate::updater() ?>

  			<!-- p class="notice">
  				IM is down at the moment.  We're working on restoring it.  Thanks for your patience!
  			</p-->


<?php JWTemplate::tab_menu() ?>

			<div class="tab">

<?php JWTemplate::tab_header( array() ) ?>

<?php 
$aStatusList = JWStatus::get_status_list_user($idUser);

JWTemplate::timeline($aStatusList) 
?>
  
<?php JWTemplate::pagination() ?>

<?php JWTemplate::rss() ?>
			</div><!-- tab -->

  			<script type="text/javascript">
//<![CDATA[  
/*new PeriodicalExecuter(function() { new Ajax.Request('/account/refresh?last_check=' + $('timeline').getElementsByTagName('tr')[0].id.split("_")[1], 
    {
      asynchronous:true, 
      evalScripts:true,
      onLoading: function(request) { Effect.Appear('timeline_refresh', {duration:0.3 }); },
      onComplete: function(request) { Element.hide('timeline_refresh'); }
    })}, 120);
*/
  //]]>
			</script>

		</div><!-- wrapper -->
	</div><!-- content -->

<?php JWTemplate::sidebar( array('status','count','jwvia','active','friend') ) ?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

