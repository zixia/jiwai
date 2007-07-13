<?php
require_once(dirname(__FILE__) . "/../jiwai.inc.php");

$memcache = JWMemcache_Tcp::Instance();

$memcache->Flush();

?>
