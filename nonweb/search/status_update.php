<?php
exit;
import de.jiwai.lucene.*;
import de.jiwai.dao.*;
error_reporting(0);
$status_index = '/opt/lucene/index/status';

$id = intval($_GET['id']);

if ( $id )
{
	$record = Execute::GetOnePK( 'Status', $id );
	$key_field = "id";
	$key_value = $id;

	$lucene_index = new LuceneIndex( $status_index );

	if ( $record ) 
	{
		/** 
		 * need utf8-encode for display;,
		 * for lucene index , no need any operation
		 */


		$token = array( true, false, false, false, false, false, false);
		$user = Execute::GetOnePK('User', $record->get("idUser"));
		$tag = Execute::GetOnePK('Tag', $record->get("idTag"));
		$other_field = array( "status", "user", "device", "mms", "tag", "time", "signature" );
		$other_value = array( 
			$record->get("status"),
			empty($user) ? '' : $user->get("nameScreen"),
			$record->get("device"),
			$record->get("isMms"),
			empty($tag) ? '' : $tag->get("name"),
			strtotime($record->get("timeCreate")),
			$record->get("isSignature"),
		);

		try {
			$lucene_index->update( $key_field, $key_value, $other_field, $other_value, $token );
			$lucene_index->flush();
			$lucene_index->close();
		}catch(Execute $e){
			die( '{"error":1,"action":"update"}' );
		}

		die ('{"error":0,"action":"update"}') ;
	}
	else
	{
		try {
			$lucene_index->delete( $key_field, $key_value );
			$lucene_index->flush();
			$lucene_index->close();
		}catch(Execute $e){
			die( '{"error":1,"action":"delete"}' );
		}

		die ('{"error":0,"action":"delete"}') ;
	}
}

die ('{"error":1,"action":"none"}') ;
?>
