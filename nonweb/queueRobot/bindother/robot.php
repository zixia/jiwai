<?php
require_once '../../../jiwai.inc.php';

class JWPubSub_Listener_BindOther implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{
		//var_dump( $data );
		//file_put_contents('/tmp/bindother.log', date('r') . serialize( $data ) . "\n", FILE_APPEND);
		$device = $data['device'];
		$sender = $data['sender'];

		if ( 'api' == $device 
			&& JWCredit::IsCreditIdUser($sender, JWCredit::CREDIT_HONOR, JWCredit::OP_LESSTHAN))
		{
			return;
		}

		$bindother = $data['bind'];
		$message = $data['message'];
		$not_reply = $data['not_reply'];
		$not_conference = $data['not_conference'];

		if ( isset($bindother['twitter']) )
		{
			if ( ('Y'==$bindother['twitter']['syncReply'] || $not_reply)
				&& ('Y'==$bindother['twitter']['syncConference'] || $not_conference) )
			{
				if ( JWBindOther::PostStatus($bindother['twitter'], $message) )
				{
					echo "[SYNC] twitter://",$bindother['twitter']['loginName']," ",strftime("%c"),"\n";
				}
				else
				{
					$this->FailMessage($bindother['twitter'], $message);
				}
			} 
		}

		if ( isset($bindother['fanfou']) )
		{
			return; // fanfou.com is down
			if ( ('Y'==$bindother['fanfou']['syncReply'] || $not_reply)
				&& ('Y'==$bindother['fanfou']['syncConference'] || $not_conference) )
			{
				if ( JWBindOther::PostStatus($bindother['fanfou'], $message) )
				{
					echo "[SYNC] fanfou://".$bindother['fanfou']['loginName']," ",strftime("%c"),"\n";
				}
				else
				{
					$this->FailMessage($bindother['fanfou'], $message);
				}
			} 	
		}
	}

	public function FailMessage($bind=array(), $message) 
	{
		echo "[FAIL] $bind[service]://$bind[loginName]\n";
		$json = json_encode( array('m'=>$message, 'b'=>$bind) );
		$base = base64_encode($json);

		error_log( "$base\n", 3, '/tmp/fail_bindother' ); 
	}
}


/* Create queue & listener **/
$queue = JWPubSub::Instance('spread://localhost/');

$listener = new JWPubSub_Listener_BindOther();
$queue->AddListener('/statuses/bindother', $listener);

/* Subscribe channel*/
$queue->Subscribe('/statuses/bindother');

/* enter main loop */
$queue->RunLoop();
?>
