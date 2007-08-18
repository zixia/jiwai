<?php
require_once('../../../jiwai.inc.php');

if ( array_key_exists('500URL',$_SESSION) )
{
	$url = $_SESSION['500URL'];
	unset ($_SESSION['500URL']);
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



JWLog::Log(LOG_CRIT, "500URL: $url");

JWTemplate::html_doctype();



?>
<head>
<?php JWTemplate::html_head()?>
</head>



  <body>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">

<style type="text/css">
table td { margin: 10px; padding:10px; }
table h2 { font-size:20px; margin:10px; padding:10px; margin-left: 0px;padding-left: 0px; line-height:120%}
table h3 { font-size:28px; margin:10px; padding:10px; margin-left: 0px;padding-left: 0px; }
table .right { text-align:top }

table ul {
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
	<table>
	<tr>
	<td class="left">
    <a href="/"><img src="<?php echo JWTemplate::GetAssetUrl('/img/system/cry.jpg')?>"/></a>
	</td>
	<td class="right">

    <h2>
<a href="/dev/">叽歪de系统核心</a>惨叫一声：
“
哇咧！我要被累死了，
你等会儿再来吧……
”
   <br />
    <br />

    <h3>你可以：</h3>
    <ul>
      <li><a href="/">返回首页</a></li>

      <li><a href="<?php echo JWTemplate::GetConst('UrlHelp')?>">查看帮助</a></li>
      <li><a href="<?php echo JWTemplate::GetConst('UrlHelpComments')?>">向我们提问</a></li>
    </ul>
	</td>
	</tr>
 	</table>


</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
