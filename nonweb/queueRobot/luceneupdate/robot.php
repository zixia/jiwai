<?php
require_once '../../../jiwai.inc.php';

class JWPubSub_Listener_LuceneUpdate implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{   
		$id = $data['id'];
		$index = $data['index'];

		JWSearch::LuceneUpdate($index, $id, true);

		echo "[LuceneUpdate]: $index://$id\n";
	}   
}


/* Create queue & listener **/
$queue = JWPubSub::Instance('spread://localhost/');

$listener = new JWPubSub_Listener_LuceneUpdate();
$queue->AddListener( '/lucene/update', $listener );

/* Subscribe channel*/
$queue->Subscribe('/lucene/update');

/* enter main loop */
$queue->RunLoop();
?>
