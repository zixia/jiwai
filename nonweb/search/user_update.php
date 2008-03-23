<?php
import de.jiwai.lucene.*;
import de.jiwai.dao.*;
error_reporting(0);
$user_index = '/opt/lucene/index/user';

$id = intval($_GET['id']);

if ( $id )
{
	$record = Execute::GetOnePK( 'User', $id );
	$key_field = "id";
	$key_value = $id;

	$lucene_index = new LuceneIndex( $user_index );

	if ( $record ) 
	{
		/** 
		 * need utf8-encode for display;,
		 * for lucene index , no need any operation
		 */

		$devices = Execute::GetArray("SELECT address FROM Device WHERE idUser=$id AND secret=''");
		$devices_value = strrev($record->get("email"));
		foreach( $devices AS $one )
		{
			$devices_value .= ' '.$one['address'];
		}

		$token = array( false, false, false, false, true, true );
		$other_field = array("nameScreen", "nameFull", "birthday", "gender", "devices", "bio");
		$other_value = array(
			$record->get("nameScreen"),
			$record->get("nameFull"),
			$record->get("birthday"),
			$record->get("gender"),
			$devices_value,
			$record->get("bio"),
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
