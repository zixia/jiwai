<?php
if ( !preg_match('/911mp3.com/i',@$_SERVER['HTTP_HOST'] ) )
{
	header ( "Location: http://911mp3.com" );
	die();
}

echo '<?xml version="1.0" encoding="UTF-8"?>';

require_once("queryhist.inc.php");

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>手机MP3 VIP下载站 / 搜索，然后下载</title>
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
<body onload="document.getElementById('query').focus();">
<h1><a href="/">手机MP3 VIP下载站</a></h1>

<h2>搜索MP3</h2>
<form action="mp3/" method="post">
<p>关键字：</p>

<p><input type="text" name="keyword" id="query"/></p>
<p><input type="submit" value="搜索，然后下载MP3" /></p>

</form>

<p>
热门歌曲：
<?php
$query_arr = load_query_hist(5);
foreach ( $query_arr as $keyword )
{
	echo "<a href='/mp3/" . urlencode($keyword) .".html'>$keyword</a>\n";
}
?>
</p>

<p>
分类目录：
<a href="/singer/">歌手目录</a>
</p>

<p><strong>手机MP3 VIP下载站：搜索，然后下载</strong></p>
<p>访问方法：在手机浏览器中输入 http://911mp3.com </p>
<p>我们愿意帮助手机访问者尽快的离开网站</p>

<h2>MP3链接</h2>

<p>5 <a href="http://911mp3.com" accesskey="5">重新搜索</a></p>

<div id="ft">&copy; 2008 手机MP3 VIP下载站</div>
</body>
</html>

