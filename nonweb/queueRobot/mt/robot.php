<?php
require_once '../../../jiwai.inc.php';

class JWPubSub_Listener_Mt implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{
		$type = $data['type'];
		$address = $data['address'];
		$message = $data['message'];
		$server_address = $data['server_address'];
		$link_id = isset($data['link_id']) ? $data['link_id'] : null;

		if ( JWRobot::SendMtRaw( $address, $type, $message, $server_address, $link_id ) )
		{
			echo "[MT]: $type://$server_address -> $type://$address\n";
		}
	}
}


/* Create queue & listener **/
$queue = JWPubSub::Instance('spread://localhost/');

$listener = new JWPubSub_Listener_Mt();
$queue->AddListener( '/robot/mt/msn', $listener );
$queue->AddListener( '/robot/mt/sms', $listener );
$queue->AddListener( '/robot/mt/mobiz', $listener );
$queue->AddListener( '/robot/mt/aol', $listener );
$queue->AddListener( '/robot/mt/fetion', $listener );
$queue->AddListener( '/robot/mt/gtalk', $listener );
$queue->AddListener( '/robot/mt/qq', $listener );
$queue->AddListener( '/robot/mt/yahoo', $listener );
$queue->AddListener( '/robot/mt/skype', $listener );
$queue->AddListener( '/robot/mt/newsmth', $listener );
$queue->AddListener( '/robot/mt/xiaoi', $listener );

/* Subscribe channel*/
$queue->Subscribe('/robot/mt/msn');
$queue->Subscribe('/robot/mt/sms');
$queue->Subscribe('/robot/mt/mobiz');
$queue->Subscribe('/robot/mt/aol');
$queue->Subscribe('/robot/mt/fetion');
$queue->Subscribe('/robot/mt/gtalk');
$queue->Subscribe('/robot/mt/qq');
$queue->Subscribe('/robot/mt/yahoo');
$queue->Subscribe('/robot/mt/skype');
$queue->Subscribe('/robot/mt/newsmth');
$queue->Subscribe('/robot/mt/xiaoi');

/* enter main loop */
$queue->RunLoop();
?>
