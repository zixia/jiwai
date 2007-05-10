<?php
define ('CONSOLE', true);

require_once(dirname(__FILE__) ."/../jiwai.inc.php");

$encoding	= 'GBK';

$from 		= "叽歪de <wo@jiwai.de>";
$to 		= "李卓桓 <lizh@aka.cn>";
$subject	= "Hello! $encoding";

$content	= <<<_HTML_
<html><h1>Hello, $encoding!</h1>
<table><tr><td>I'm</td><td>Boy</td></tr><tr><td>I'm</td><td>Gril</td></tr></table>
</html>
_HTML_;

$user = JWUser::GetUserInfo(1);
$friend = JWUser::GetUserInfo(11);

JWMail::SendMailNoticeNewFriend($user,$friend);

//JWMail::SendMail($from, $to, $subject, $content, array ( 'encoding'	=> $encoding ));
?>
