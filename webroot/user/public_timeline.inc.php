<?php
JWTemplate::html_doctype();
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 

$status_data 	= JWStatus::GetStatusIdsFromPublic(100);
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
					,'refresh_time'	=> '120'
					,'refresh_url'	=> ''
			);

?>
<head>
<?php JWTemplate::html_head($options) ?>
</head>

<body class="front" id="front">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator">
	<div class="note1">
	<h2>一句话博客</h2>
	<p>用只言片语串成生活轨迹</p>
	</div>
	<div class="note2">
	<h2>分享与沟通</h2>
	<p>关心朋友的一举一动</p>
	</div>
	<div class="note3">
	<h2>贴身叽歪</h2>
	<p>手机/QQ/MSN/GTalk/Skype</p>
	</div>
</div>

<div id="container">
	<div id="content">
		<div id="wrapper">

<?php JWTemplate::ShowActionResultTips(); ?>

			<div class="tab">

<?php 
JWTemplate::tab_header( array( 
	'title' => '看看大家都在叽歪什么...',
	'title2' => '', //你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢？：-）'
));

$options = array (
	'uniq' => 2,
	'nummax' => 20,
);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, $options) 
?>
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->

<?php 

$newest_options['title']		= '看看新来的';
$newest_options['user_ids']		= JWUser::GetNewestUserIds(5);
$newest_options['view']			= 'list';

$blog_options['user_name']	= 'blog';
$blog_options['title']		= '叽歪de博客最新主题';

$announce_options['user_name']	= 'team';
$announce_options['title']		= '公告';

$featured_options	= array( 'user_ids' => JWUser::GetFeaturedUserIds(12) );

$arr_menu = array(	
					array ('announce'		, array($announce_options) )
					,array ('separator'		, array() )
					,array ('announce'		, array($blog_options) )
					,array ('separator'		, array() )
					,array ('featured'		, array($featured_options) )
					,array ('separator'		, array() )
					,array ('featured'		, array($newest_options) )
				);

if ( ! JWLogin::IsLogined() ) {
	array_unshift ($arr_menu, array('separator', array()));
	array_unshift ($arr_menu, array('register', array(true)));
	array_unshift ($arr_menu, array('login', array()));
}
//array_unshift($arr_menu, array ('head'	, array('JiWai.de <strong>叽歪广场</strong>')));

JWTemplate::sidebar($arr_menu, null);
JWTemplate::container_ending();
?>

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
