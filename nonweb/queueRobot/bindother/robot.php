<?php
require_once '../../../jiwai.inc.php';

class JWPubSub_Listener_BindOther implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{
		$device = $data['device'];
		$bindother = $data['bind'];

		if ( 'api' == $device && JWCredit::IsCreditIdUser($bindother['idUser'], JWCredit::CREDIT_HONOR, JWCredit::OP_NOTLESSTHAN))
			return;

		$message = $data['message'];
		$not_reply = $data['not_reply'];
		$not_conference = $data['not_conference'];

		if ( isset($bindother['twitter']) )
		{
			if ( ('Y'==$bindother['twitter']['syncReply'] || $not_reply)
				&& ('Y'==$bindother['twitter']['syncConference'] || $not_conference) )
			{
				JWBindOther::PostStatus($bindother['twitter'], $message);
				echo "[SYNC] twitter://".$bindother['twitter']['loginName']."\n";
			} 
		}

		if ( isset($bindother['fanfou']) )
		{
			if ( ('Y'==$bindother['fanfou']['syncReply'] || $not_reply)
				&& ('Y'==$bindother['fanfou']['syncConference'] || $not_conference) )
			{
				JWBindOther::PostStatus($bindother['fanfou'], $message);
				echo "[SYNC] fanfou://".$bindother['fanfou']['loginName']."\n";
			} 	
		}
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
