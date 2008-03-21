<?php
import de.jiwai.lucene.*;
import de.jiwai.dao.*;

$user_index = '/opt/lucene/index/user';

echo "<pre>";

$id = intval($_GET['id']);

if ( $id )
{
	$record = Execute::GetOnePK( 'User', $id );
	if ( $record ) 
	{

		/** 
		 * need utf8-encode for display;,
		 * for lucene index , no need any operation
		 */
		$lucene_index = new LuceneIndex( $user_index );
		$key_field = "id";
		$key_value = $record->get("id");

		$token = array( false, false, false, false, true );
		$other_field = array("nameScreen", "email", "birthday", "gender", "bio");
		$other_value = array(
			$record->get("nameScreen"),
			strrev($record->get("email")),
			$record->get("birthday"),
			$record->get("gender"),
			$record->get("bio"),
		);

		var_dump( $other_field );
		var_dump( $other_value );
		var_dump( $key_value );
		var_dump( $token );

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
