<?php
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>手机MP3 VIP下载站 / 搜索，然后下载！</title>
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

<h2>搜索MP3</h2>
<form action="/mp3/" method="post">
<p>关键字：</p>

<p><input type="text" name="keyword"/></p>
<p><input type="submit" value="搜索，然后下载MP3！" /></p>

</form>

<?php
$path = $_REQUEST['path'];

$param_array = split('/',$path);

$type 			= $param_array[1];
$alpha_index	= $param_array[2];


$link = new mysqli('10.1.50.10','911mp3','911mp3dem1ma','911mp3');

if (mysqli_connect_errno())
	die('mysql connect error');

if (!$link->set_charset("utf8"))
	die('mysql set utf8 error');

if ( !$type )
{
	// have nothing, display type index

	echo "<p><b>歌手分类索引：</b></p>\n";

	$result = $link->query('select distinct Type from Singer_TB');

	echo "<p>\n";
	while ( $row=$result->fetch_assoc() )
	{
		$type = $row['Type'];
		echo "<a href='/singer/" . urlencode($type) . "/'>$type</a>\n";
	}
	echo "</p>\n";
}else if ( !$alpha_index )
{
	// have type, no alpha index
	echo "<p><b>歌手分类索引：$type</b></p>\n";

	$result = $link->query("select distinct AlphaIndex from Singer_TB where Type='$type'");

	echo "<p>\n";
	while ( $row=$result->fetch_assoc() )
	{
		$index = $row['AlphaIndex'];
		echo "<a href='/singer/" . urlencode($type) . "/$index/'>$index</a>\n";
	}
	echo "</p>\n";
}else{
	// have type & alpha index
	echo "<p><b>歌手分类索引：$type/$alpha_index</b></p>\n";

	$result = $link->query("select Name from Singer_TB where Type='$type' and AlphaIndex='$alpha_index'");

	echo "<p>\n";
	while ( $row=$result->fetch_assoc() )
	{
		$name = $row['Name'];
		echo "<a href='/mp3/" . urlencode($name) . "/'>$name</a>\n";
	}
	echo "</p>\n";
}

$link->close();;
?>
</p>

<p><strong>手机MP3 VIP下载站：搜索，然后下载！</strong></p>
<p>我们愿意帮助手机访问者尽快的离开网站</p>

<h2>MP3链接</h2>

<p>5 <a href="/" accesskey="5">重新搜索</a></p>

<div id="ft">&copy; 2008 手机MP3 VIP下载站</div>
</body>
</html>

