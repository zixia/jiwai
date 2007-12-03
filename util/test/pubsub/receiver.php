<?php
require_once '../../../jiwai.inc.php.dist';
class MyListener implements JWPubSub_Listener {
	function onData($channel, $data) {
		echo "==== onData from $channel ====\n";
		var_dump($data);
	}
}
$l = new MyListener();
//$pubsub = JWPubSub::Instance('file://localhost/tmp');
$pubsub = JWPubSub::Instance('sysv://localhost:1/');
//$pubsub = JWPubSub::Instance('spread://localhost:4803/');
$pubsub->AddListener('/test/chan1', $l);
$pubsub->RunLoop();
?>
