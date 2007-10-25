<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>叽歪de / 大中国 / 今天你叽歪了吗？</title>
	<meta http-equiv="pragma" content="no-cache"/> 
	<meta http-equiv="cache-control" content="no-cache, must-revalidate"/> 
	<meta http-equiv="expires" content="0"/> 

	<meta name="keywords" content="叽歪网,唧歪网,叽歪de,叽歪的,矶歪de,唧歪de,唧歪的,叽叽歪歪,唧唧歪歪,迷你博客,碎碎念,絮絮叨叨,絮叨,jiwaide,tiny blog,im nick" />
	<meta name="description" content="叽歪de - 通过手机短信、聊天软件（QQ/MSN/GTalk）和Web，进行组建好友社区并实时与朋友分享的迷你博客服务。" />
	<meta name="author" content="叽歪de &lt;wo@jiwai.de&gt;" />

<link rel="alternate"  type="application/rss+xml" title="叽歪de - [RSS]" href="http://feed.blog.jiwai.de" />


	<style type="text/css">
	*{margin:0;padding:0;}
	html,body,#map{height:100%;}
	body {font:small/1.5 arial,helvetical,sans-serif;}
	.entry {position:relative; padding:0 10px 0 60px;}
	.entry a img {position:absolute; left:0; _left:-60px; top:0; border:0;}
	.entry p {font-size:medium; font-weight:bold;}
	.s1 {color:#f93}
	.s2 {color:#060}
	.s3 {color:#39f}
	.s4 {color:#f09}
	.s5 {color:#026908}
	.s6{color:#186c6b}
	.s7{color:#b6606b}
	.s8{color:#a1725b}
	.s9{color:#29528c}
	.s10{color:#e15492}

    #info {position:absolute; right:500px; bottom:6px;}
	</style>
    <script type="text/javascript" src="http://ditu.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAgXaBn43IYnxRSoaoNZq6LhQeTo5oc3oCUKc4c2RXSFS_CE5MUxTEjY8-4KuDNw6rhm434wLn7tP3Ow"></script>

	<script type="text/javascript" src="jiwai-map.js" charset="UTF-8"></script>
  </head>

  <body onunload="GUnload()">

    <div id="map"></div>

      <div id="info">
          今天你<a href="http://jiwai.de/" target="_blank">叽歪</a>了吗？
      </div>

<?php
JWTemplate::GoogleAnalytics()
?>

  </body>
</html>
