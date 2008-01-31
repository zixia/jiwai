<?php
require_once '../../../jiwai.inc.php';
JWMemcache::Instance()->SetUseLocalCache(false);

class JWPubSub_Listener_StatusUpdate implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{
		$data['type'] = JWNotifyQueue::T_STATUS;
		JWNotify::NotifyStatus( $data );
	}
}


/* Create queue & listener **/
$queue = JWPubSub::Instance('spread://localhost/');

$listener = new JWPubSub_Listener_StatusUpdate();
$queue->AddListener( '/statuses/update', $listener );

/* Subscribe channel*/
$queue->Subscribe('/statuses/update');

/* enter main loop */
$queue->RunLoop();
?>
