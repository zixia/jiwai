<?php
require_once '../../../jiwai.inc.php';

class JWPubSub_Listener_SmsInvite implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{
		$type = 'sms';

		$user_id = $data['user_id'];
		$address = $data['address'];
		$message = $data['message'];
		
		$device_row = JWDevice::GetDeviceDbRowByAddress( $address, $type );
		if ( empty( $device_row ) ) 
		{
			$invite_code = JWDevice::GenSecret( 8 );
			JWInvitation::Create($user_id, $address, $type, $message, $invite_code);
		}
		$message .= (false===strpos($message, ' F ')) ? ' 请回复 F 确定' : '';

		$server_address = JWFuncCode::GetSmsInviteFunc($address, $user_id);
		
		JWPubSub::Instance('spread://localhost/')->Publish( '/robot/mt/sms', array(
			'type' => $type,
			'address' => $address,
			'message' => $message,
			'server_address' => $server_address,
		));

		echo "[SMSINVITE]: user://$user_id => sms://$address\n";
	}
}


/* Create queue & listener **/
$queue = JWPubSub::Instance('spread://localhost/');

$listener = new JWPubSub_Listener_SmsInvite();
$queue->AddListener('/invite/sms', $listener);

/* Subscribe channel*/
$queue->Subscribe('/invite/sms');

/* enter main loop */
$queue->RunLoop();
?>
