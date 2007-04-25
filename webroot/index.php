<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('jiwai.inc.php');
JWDebug::init();

if ( JWUser::IsLogined() )
	header('Location: /wo/');
?>
<html>

<?php JWTemplate::html_head() ?>


<body class="account" id="front">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">

			<h2>
</h2>

			<h2>叽歪de是一个汇聚了朋友和陌生人的社区，大家在这里回答一个简单的问题：<em>这一刻，你在做什么？</em>通过手机发送短信，QQ，MSN，或直接登录jiwai.de网站，即时记下你想说的话。</h2>

			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'看看<a href="' 
													. JWTemplate::GetConst('UrlPublicTimeline')
													. '">大家</a>都在忙些什么？'
									, 'title2'	=>	'叽叽歪歪' 
							) )
?>

<?php 
$aStatusList = JWStatus::get_status_list_timeline();
JWTemplate::timeline($aStatusList) 
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php JWTemplate::sidebar( array('login_notice','login','register','featured') ) ?>
			
		
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
