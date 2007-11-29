<?php
interface JWPubSub_Listener {
	function onData($channel, $data);
}
?>
