<?php

/*
if ( !@$_GET['keyword'] && @$_POST['keyword'] )
{
	header("Location: /mp3/" . urlencode(trim($_POST['keyword'])) . ".html");
	die();
}
*/

echo '<?xml version="1.0" encoding="UTF-8"?>';

require_once("curl.inc.php");
require_once("queryhist.inc.php");

$keyword = trim($_REQUEST['keyword'], ' /');
//$keyword = '过火';

save_query_hist($keyword);

$status_log = "";


?><!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>搜索下载“<?php echo $keyword; ?>”音乐MP3 - 手机MP3 VIP下载站 - 搜索，然后下载</title>
<meta name="keywords" content="手机歌曲,手机歌曲下载,手机mp4歌曲下载,手机mp3歌曲下载,手机如何下载歌曲,手机怎么下载歌曲,手机怎样下载歌曲" />
<meta name="description" content="本站提供手机歌曲下载,在线试听歌曲,并可以下载到手机上,手机歌曲,手机歌曲下载,手机mp4歌曲下载,手机mp3歌曲下载,手机如何下载歌曲,手机怎么下载歌曲,手机怎样下载歌曲,怎样用手机下载歌曲,怎样下载歌曲到手机,如何下载歌曲到手机" />

<style type="text/css">
body,ul,ol,form{margin:0 0;padding:0 0}
ul,ol{list-style:none}
h1,h2,h3,div,li,p{margin:0 0;padding:2px;font-size:medium}
h2,li,.s{border-bottom:1px solid #ccc}
h1{background:#FF8A00; height:26px;}
h2{background:#EEEEEE}
.n{border:1px solid #ffed00;background:#fffcaa}
.t,.a,.stamp,#ft{color:#999;font-size:small}
a{color:#C55400;}
img{border:0px;}
h1 a{color:#FFFFFF; text-decoration:none;}
</style>
</head>
<body>
<h1><a href="/">手机MP3 VIP下载站</a></h1>

<?php
// only one support.
// google_adsense();
?>

<h2>搜索MP3 - 关键字：<?php echo $keyword; ?></h2>

<?php
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

$search_url = "http://www.1g1g.com/list/load.jsp?encoding=utf8&type=search&query=" . urlencode($keyword);
$search_key	= "51MP3_SEARCH_" . md5($search_url);

$content 	= $memcache->get($search_key);

$content = "";

if ( !$content )
{
	$magic_key  = "51MP3_MAGIC";
	$magic_no	= $memcache->get($magic_key);

	//$status_log .= "magic=$magic_no|";
	
	$cookie_key = "51MP3_COOKIE";
	$cookie_var = $memcache->get($cookie_key);
	//$status_log .= "cookie=$cookie_var|";

	$content 	= http_request( "$search_url&magic=$magic_no", $cookie_var );


	//die( "$search_url&magic=$magic_no, $cookie_var" );
//die(var_dump($http_info));

	//die($content);
	//die( "HH: $search_url&magic=$magic_no");

	//error_log($content);

	try
	{
		$xml = new SimpleXMLElement( $content );
	}catch ( Exception $e ){
		error_log( "search return not xml but: [$content]" );
	}
	
	//die(var_dump($xml));

	if ( "0000"!=$xml->result['resultCode'] )
	{
		$seed 		= $xml->seed;
		
		//$status_log .= "[./1g1gmagic $seed]";

		$handle		= popen("./1g1gmagic $seed", "r");
		$magic_no 	= fread( $handle, 4096 );
		pclose ( $handle ) ;

		//die("$seed - [$magic_no]");
		$cookie = "";
      	foreach ($cookiearr as $key=>$value)
      	{
        	$cookie .= "$key=$value; ";
      	}

		//die( "$search_url&magic=$magic_no " .  $cookie );
		$content = http_request( "$search_url&magic=$magic_no", $cookie );

		$xml = '';
		try
		{
			$xml = new SimpleXMLElement( $content );
		}catch(Exception $e){
			error_log( "2nd search return not xml but: [$content]" );
		}

		$memcache->set($magic_key	, $magic_no	, false, 604800) or die ("Failed to save data at the server for magic");
		$memcache->set($cookie_key	, $cookie	, false, 604800) or die ("Failed to save data at the server for cookie");
    }

	$memcache->set($search_key, $content, MEMCACHE_COMPRESSED, 86400) or die ("Failed to save data at the server");
}else{
	//echo "HIT\n";
}

//die("[$content]");
//echo $xml->song;

$song_link_list = array();

$song_link_info_cached = array();

foreach ($xml->songlist->song as $song) 
{
	foreach ($song->source->link as $link) 
	{
		$link = ''.$link;

		$link_key	= "51MP3_LINK_" . md5($link);
		$http_info 	= $memcache->get($link_key);

		if ( !$http_info 
// if conn failed, we also cache it.
//				|| !preg_match('/^\d\d\d$/',$http_info['http_code']) 
			)
		{
			$song_link_list[$link] = $link;
		}else{
			$song_link_info_cached[$link] = $http_info;

			// HIT
			//echo "<!-- http_code of $link - $http_info[http_code] -->\n";
		}
//print_r($http_info);
//memcache_debug(true);
	
	}
}

//die(var_dump($song_link_list));
//die(var_dump($xml));

$song_link_info = multiRequestHead($song_link_list);

foreach ( $song_link_list as $link )
{
	$link = ''.$link;

	$link_key	= "51MP3_LINK_" . md5($link);
	

	$memcache->set($link_key, $song_link_info[$link], false, 604800) or die ("Failed to save link data at the server.");
//die(var_dump($song_link_info));
}

$status_log .= "link_hit=" . count($song_link_info_cached) . "|";
$status_log .= "link_unhit=" . count($song_link_info) . "|";

$song_link_info = array_merge($song_link_info,$song_link_info_cached);

//die(var_dump($song_link_info));

foreach ($xml->songlist->song as $song) 
{
	$valid = false;

	$n = 1;
	foreach ($song->source->link as $link) 
	{
		$filesize 	= $link['filesize'];
		$format		= $link['format'];


		$link 		= '' . $link;

		$http_info	= $song_link_info[$link];

		$http_size 	= $http_info['download_content_length'];
		$http_code 	= $http_info['http_code'];

		//echo "<a href='$link'>#${n}下载地址 $filesize @ $format</a> http_size: $http_size\n";

		//echo "<!-- $link $filesize $song->name $http_code-->\n";
		if ( 200!=$http_code 
//				|| $filesize!=$http_size 
				)
		{
			continue;
		}

		if ( !$valid )
		{
			$valid = true;

			echo "<p>\n";
			echo "<a href='/mp3/" . urlencode($song->name) 		. ".html'>" . htmlspecialchars($song->name) 	. "</a><br />";
			echo "<a href='/mp3/" . urlencode($song->singer) 	. ".html'>" . htmlspecialchars($song->singer) 	. "</a><br />";
			echo "<a href='/mp3/" . urlencode($song->album)		. ".html'>" . htmlspecialchars($song->album)	. "</a><br />";
		}


		$filesize = floor($filesize/1024/1024*100)/100;
		echo "<a rel='nofollow' href='$link'>#${n}下载$format(${filesize}MB)</a><br />\n";
		$n++;

/*
		echo $source->filesize, '<br />';
		echo $source->format, '<br />';
		echo $source->content, '<br />';
*/

	}

	if ( $valid )
		echo "</p><hr />\n";
}

?>


<?php
require_once("adsense.inc.php");
?>


<p><strong>手机MP3 VIP下载站：搜索，然后下载</strong></p>
<p>我们致力于帮助手机访问者尽快离开这里</p>

<h2>MP3链接</h2>

<p>5 <a href="http://911mp3.com" accesskey="5">重新搜索</a></p>


<div id="ft">&copy; 2008 手机MP3 VIP下载站 
<?php
if ( isset($_SERVER['HTTP_ACCEPT']) 
		&& (strpos($_SERVER['HTTP_ACCEPT'],'vnd.wap.wml')!==FALSE) 
	)
{
	// mobile
}else{
	echo "<a href='http://jiwai.de/goby/thread/12164600/12164600' target='_blank'>留言板</a>\n";
}
?>
</div>


<?php
if ( isset($_SERVER['HTTP_ACCEPT']) 
		&& (strpos($_SERVER['HTTP_ACCEPT'],'vnd.wap.wml')!==FALSE) 
	)
{
	// mobile
}else{
	// pc
?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-287835-14");
pageTracker._trackPageview();
</script>
<?php
}
?>

<div><!-- <?php echo $status_log ?> --></div>
</body>
</html>

