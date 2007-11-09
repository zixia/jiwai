<?php
Header("Content-type: text/plain,charset=gb2312"); 
header("Content-Disposition: attachment; filename=\"jiwai_share.reg\"" );

$serverName = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'JiWai.de';
$name = mb_convert_encoding( '收藏到叽歪', 'GB2312', 'UTF-8' );

echo <<<_HTML_
REGEDIT4

[HKEY_CURRENT_USER\Software\Microsoft\Internet Explorer\MenuExt]

[HKEY_CURRENT_USER\Software\Microsoft\Internet Explorer\MenuExt\\${name}]
@="http://$serverName/wo/share/menuext"

_HTML_;
?>
