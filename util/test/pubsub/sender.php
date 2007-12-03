<?php
require_once '../../../jiwai.inc.php.dist';
//$pubsub = JWPubSub::Instance('file://localhost/tmp');
$pubsub = JWPubSub::Instance('sysv://localhost:1/');
//$pubsub = JWPubSub::Instance('spread://localhost:4803/');
$obj = array('razor'=>rand());
$pubsub->Publish('/test/chan1', $obj);
/*
$x = '"x"';
var_dump(json_decode($x));
//PHP 5.1.6 bug if NULL is given!
*/
?>
