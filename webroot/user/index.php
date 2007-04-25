<?php
require_once('../jiwai.inc.php');
//var_dump($_REQUEST);

$nameScreen	= @$_REQUEST['nameScreen'];
$pathParam 	= @$_REQUEST['pathParam'];

//die(var_dump($_GET));
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if ( 'dajia'===strtolower($nameScreen) )
{
	require_once(dirname(__FILE__) . '/dajia.php');
	exit(0);
}

$idUser = JWUser::GetUserInfoByName($nameScreen,'id');

if ( null===$idUser )
{
	$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];
	header ( "Location: " . JWTemplate::GetConst("UrlError404") );
	exit(0);
}

switch ( $func )
{
	case 'picture':
		require_once(dirname(__FILE__) . "/picture.php");

		// get rid of file ext and dot: we know what type it is.
		$param = preg_replace('/\.[^.]*$/','',$param);

		user_picture( $idUser, $param);

		exit(0);

	default:
		break;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
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
	<!-- div id="flaginfo">zixia</div -->
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">


<?php 
if ( array_key_exists('nameScreen',$_REQUEST) ){
	$nameScreen = $_REQUEST['nameScreen'];
	$aStatusList = JWStatus::get_status_list_user($idUser);
}else{
	$aStatusList = null;
}

	if ( isset($aStatusList) )
		JWTemplate::status_head(array_shift($aStatusList)); 
?>

<?php JWTemplate::tab_menu() ?>

			<div class="tab">

<?php JWTemplate::tab_header( array() ) ?>

<?php 
JWTemplate::timeline($aStatusList, false) 
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

<?php 
$arr_menu = array(	'user_notice'
					, 'user_info'
					, 'count'
					, 'friend'
				);

if ( ! JWUser::IsLogined() )
	array_push ($arr_menu, 'register');

JWTemplate::sidebar( $arr_menu, $idUser);
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
