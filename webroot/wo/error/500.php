<?php
require_once('../../jiwai.inc.php');


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
	//header('Location: /');
	//exit(0);
}

echo <<<_HTML_
<a href="/dev/">叽歪de系统核心</a>惨叫一声：
“
哇咧！我要被累死了，
你等会儿再来吧……
”
_HTML_;
?>
