<?php
JWTemplate::html_doctype();
?>
<html>

<?php 

$status_data 	= JWStatus::GetStatusIdsFromPublic(30);
$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

$keywords 		= '叽歪de广场 ';
$user_showed 	= array();
foreach ( $user_rows  as $user_id=>$user_row )
{
	if ( isset($user_showed[$user_id]) )
		continue;
	else
		$user_showed[$user_id] = true;

	$keywords .= "$user_row[nameScreen]($user_row[nameFull]) ";
}

$description = '叽歪de广场 ';
foreach ( $status_data['status_ids'] as $status_id )
{
	$description .= $status_rows[$status_id]['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}

$options = array(	 'title'		=> '叽歪广场'
					,'keywords'		=> htmlspecialchars($keywords)
					,'description'	=> htmlspecialchars($description)
					,'author'		=> htmlspecialchars($keywords)
					,'rss_url'		=> 'http://api.jiwai.de/status/public_timeline.rss'
					,'rss_title'	=> '叽歪de - 叽歪广场 [RSS]'
					,'refresh_time'	=> '60'
					,'refresh_url'	=> ''
			);

JWTemplate::html_head($options) ;
?>


<body class="status" id="public_timeline">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">


			<span class="ytf" style="font-family: 黑体">
				<h2><a href="http://help.jiwai.de/NewUserGuide" target="_blank">第一次来，不知道如何叽歪？很容易，来这里吧！</a></h2>
			</span>
			<script type="text/javascript">
				JiWai.Yft(".ytf");
			</script>


<?php JWTemplate::ShowActionResultTips(); ?>


			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'最新动态 - 大家在做什么？' 
									, 'title2'	=>	''//你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢？：-）'
							) )
?>

<?php 

$options	= array ( 'uniq'=>2 );
JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, $options) 
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php 

$newest_options['title']		= '看看新来的';
$newest_options['user_ids']		= JWUser::GetNewestUserIds(5);

$blog_options['user_name']	= 'blog';
$blog_options['title']		= '叽歪de博客最新主题';

$announce_options['user_name']	= 'team';
$announce_options['title']		= '公告';

$featured_options	= array( 'user_ids' => JWUser::GetFeaturedUserIds() );

$arr_menu = array(	array ('head'			, array('JiWai.de <strong>叽歪广场</strong>'))
					, array ('announce'		, array($announce_options) )
					, array ('announce'		, array($blog_options) )
					, array ('featured'			, array($featured_options) )
					, array ('featured'			, array($newest_options) )
				);

if ( ! JWLogin::IsLogined() )
	array_push ($arr_menu, array('register', array(true)));

JWTemplate::sidebar($arr_menu, null);
?>
			
		
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
