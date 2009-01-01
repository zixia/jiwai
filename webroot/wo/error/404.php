<?php
require_once('../../../jiwai.inc.php');

if ( array_key_exists('404URL',$_SESSION) )
{
	$url = $_SESSION['404URL'];
	unset ($_SESSION['404URL']);
}
else if ( isset($_SERVER['REDIRECT_SCRIPT_URI']) )
{
	$url = $_SERVER['REDIRECT_SCRIPT_URI'];
}
else if ( isset($_SERVER['HTTP_REFERER']) )
{
	$url = $_SERVER['HTTP_REFERER'];
}

if ( empty($url) )
{
	header('Location: /');
	exit(0);
}

JWLog::Log(LOG_CRIT, "404URL: $url");

/* Return the Time Diff */
define ('MINUTE_SECS', 60);
define ('HOUR_SECS', MINUTE_SECS * 60);
define ('DAY_SECS', HOUR_SECS * 24);

function getTimeDiff($now, $future) {
    $diff = $future - $now;

    if ($diff <= 0) {
        return '0天0小时0分钟';
    }

    $ret = array();

    $ret['day'] = (int)($diff / DAY_SECS); $diff -= DAY_SECS * $ret['day'];
    $ret['hour'] = (int)($diff / HOUR_SECS); $diff -= HOUR_SECS * $ret['hour'];
    $ret['minute'] = (int)($diff / MINUTE_SECS); $diff -= MINUTE_SECS * $ret['minute'];
    $ret['second'] = (int)$diff;

    return $ret['day'] . "天" . $ret['hour'] . "小时" . $ret['minute'] . "分钟";
}

$element = JWElement::Instance();
?>
<?php $element->html_header(); ?>
<?php $element->common_header(); ?>

<style type="text/css">
#container table td { margin: 10px; padding:10px; }
#container table h2 { font-size:20px; margin:10px; padding:10px; margin-left: 0px;padding-left: 0px; }
#container table h3 { font-size:28px; margin:10px; padding:10px; margin-left: 0px;padding-left: 0px; }
#container table .right { text-align:top }

#container table ul {
margin: 20px;
font-size: 1.5em;
list-style-image:none;
list-style-position:outside;
list-style-type:circle;
}

table li {
margin: 10px;
}

</style>
<STYLE type=text/css>BODY {
	BACKGROUND:  #ffffff fixed left top
}
#err404{
	margin: 20px auto;width:776px;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:32px; color:#ff6600; line-height:32px; font-weight:bold; 
}
#err404 .countDown{
	padding-bottom:50px; padding-left:30px;
}
#err404 a:link{color:#ff6600; text-decoration:none; line-height:50px; font-size:28px;}
#err404 a:visited{color:#ff6600; text-decoration:none; line-height:50px; font-size:28px;}
#err404 a:hover{color:#ffffff; text-decoration:none; line-height:50px; background-color:#ff6600; font-size:28px;}
#err404 a:active{color:#ffffff; text-decoration:none; line-height:50px; background-color:#ff6600; font-size:28px;}
</STYLE>

<div id="container">
<table border="0" cellpadding="0" cellspacing="0" id="err404">
  <tr>
    <td width="381" rowspan="2"><img src="http://asset.jiwai.de/images/org-404-left.jpg" width="381" height="484"></td>
    <td><img src="http://asset.jiwai.de/images/org-404-right.jpg" width="381" height="399"></td>
  </tr>
  <tr>
    <td class="countDown"><?php echo getTimeDiff(time(), strtotime('2012/7/27 12:00:00')); ?></td>
  </tr>
    <tr align="center" >
    <td style="text-align:right; padding-right:25px; "><a href="<?php echo $url;?>">&lt;&lt; 从东土大唐来</a></td>
    <td style="text-align:left; padding-left:25px; "><a href="http://jiwai.de/">到西天取经去 &gt;&gt; </a></td>
  </tr>
  <tr>
  <style type="text/css">
#goog-wm {
  padding: 1em;
  border: 3px solid #ff6600;
  background-color: white;
}
#goog-wm h3#closest-match {
  color: #8f2831;
  border-bottom: 3px dashed #ff6600;
  padding-bottom: 0.5em;;
  font-size: 170%;
  margin: 0;
}
#goog-wm h3#closest-match a { }
#goog-wm h3#other-things { color: #8f2831; }
#goog-wm ul li { }
#goog-wm li.search-goog { display: block; }
  </style>
  <script type="text/javascript">
  var GOOG_FIXURL_LANG = 'zh_CN';
  var GOOG_FIXURL_SITE = 'http://jiwai.de/';
  </script>
  <script type="text/javascript" 
  src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>
  </tr>
</table>

</div><!-- #container -->


<?php $element->common_footer();?>
<?php $element->html_footer();?>
