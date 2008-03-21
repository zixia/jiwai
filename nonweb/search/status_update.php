<?php
import de.jiwai.lucene.*;
import de.jiwai.dao.*;

$status_index = '/opt/lucene/index/status';

echo "<pre>";

$id = intval($_GET['id']);

if ( $id )
{
	$record = Execute::GetOnePK( 'Status', $id );
	if ( $record ) 
	{

		/** 
		 * need utf8-encode for display;,
		 * for lucene index , no need any operation
		 */
		$lucene_index = new LuceneIndex( $status_index );
		$key_field = "id";
		$key_value = $record->get("id");

		$token = array( true );
		$other_field = array("status");
		$other_value = array(
			$record->get("status"),
		);

		$lucene_index->update( $key_field, $key_value, $other_field, $other_value, $token );
		//$lucene_index->delete( $key_field, $key_value );
		$lucene_index->flush();
		$lucene_index->close();

		echo '{"error":0}';
		exit;
	}
}

echo '{"error":1}';
exit;
?>
