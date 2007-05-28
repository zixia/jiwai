<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../jiwai.inc.php');
//JWDebug::init();

if ( JWLogin::IsLogined() )
	header('Location: /wo/');
?>
<html>

<?php 
JWTemplate::html_head();
?>


<body class="front" id="front">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">


			<h2 style="font-size:18px">拥有你自己的语录，直播你的生活和思想：<em>这一刻，你在做什么？</em>通过手机发送短信，QQ，MSN，Gtalk或直接登录叽歪de网站，即时记录点滴瞬间。You are the reporter, and let us be your media.</h2>

			<br>
			<span class="ytf" style="font-family: 黑体">
				<h2 style="font-size:18px"><a href="http://help.jiwai.de/NewUserGuide" target="_blank">第一次来，不知道如何叽歪？很容易，来这里吧！</a></h2>
			</span>
			<script type="text/javascript">
				JiWai.Yft(".ytf");
			</script>



			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'看看<a href="' 
													. JWTemplate::GetConst('UrlPublicTimeline')
													. '">大家</a>都在做些什么？'
									, 'title2'	=>	'' 
							) )
?>

<?php 
$status_data = JWStatus::GetStatusIdsFromPublic(30);

$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

$options		= array ( 'uniq' => 1 );
JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, $options);
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php 
$featured_options	= array( 'user_ids' => JWUser::GetFeaturedUserIds() );

$newest_options['title']		= '看看新来的';
$newest_options['user_ids']		= JWUser::GetNewestUserIds(5);

$blog_options['user_name']	= 'blog';
$blog_options['title']		= '最新博文';

$announce_options['user_name']	= 'team';
$announce_options['title']		= '公告';


$arr_menu 	= array( array ('head'			, array('<h3>请登陆！</h3>'))
					, array ('login'		, null)
					, array ('register'		, null)
					, array ('announce'		, array($announce_options) )
					, array ('announce'		, array($blog_options) )
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
