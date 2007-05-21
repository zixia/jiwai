<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../jiwai.inc.php');
//JWDebug::init();

if ( JWLogin::IsLogined() )
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


			<h2>叽歪de是一个汇聚了朋友和陌生人的社区，大家在这里回答一个简单的问题：<em>这一刻，你在做什么？</em>通过手机发送短信，QQ，MSN，或直接登录jiwai.de网站，即时记下你想说的话。</h2>

			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'看看<a href="' 
													. JWTemplate::GetConst('UrlPublicTimeline')
													. '">大家</a>都在忙些什么？'
									, 'title2'	=>	'叽叽歪歪' 
							) )
?>

<?php 
$status_data = JWStatus::GetStatusIdsFromPublic();

$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows);
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php 
$featured_options['user_ids']	= JWUser::GetFeaturedUserIds(5);

$newest_options['title']		= '看看新来的';
$newest_options['user_ids']		= JWUser::GetNewestUserIds(5);


$arr_menu 	= array( array ('head'			, array('<h3>请登陆！</h3>'))
					, array ('login'		, null)
					, array ('register'		, null)
					, array ('featured'		, array($featured_options) )
					, array ('featured'		, array($newest_options) )
				);

JWTemplate::sidebar($arr_menu, null) ;

?>
			
		
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
