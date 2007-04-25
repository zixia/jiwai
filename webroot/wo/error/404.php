<?php
require_once('../../jiwai.inc.php');

if ( array_key_exists('404URL',$_SESSION) )
{
	$url = $_SESSION['404URL'];
	unset ($_SESSION['404URL']);
}
else if ( isset($_SERVER['HTTP_REFFER']) )
{
	$url = $_SERVER['HTTP_REFFER'];
}
else if ( isset($_SERVER['REDIRECT_SCRIPT_URI']) )
{
	$url = $_SERVER['REDIRECT_SCRIPT_URI'];
}

if ( empty($url) )
{
	header('Location: /');
	exit(0);
}

echo "哎呀！我找不到 $url 啦！:-(";
?>
