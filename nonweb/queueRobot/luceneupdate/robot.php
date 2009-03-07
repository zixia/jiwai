<?php
/**
 * Only run this script in LuceneIndex machine.
 */
require_once '../../../jiwai.inc.php';

class JWPubSub_Listener_LuceneUpdate implements JWPubSub_Listener
{
	public function OnData($channel, $data)
	{   
		$id = $data['id'];
		$index = $data['index'];

		$file = "/tmp/update_${index}";
		error_log( "$id\n", 3, $file );
		echo "[LuceneUpdate]: $index://$id\n";

		if ( $index == 'status' ) {
			file_get_contents("http://localhost:8080/status_update.php?id={$id}");
		}
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
