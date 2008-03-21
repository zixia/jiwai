<?php
import de.jiwai.lucene.*;
$user_index = '/opt/lucene/index/statuss';
$key_field = 'id';
$order_field = null;

/*
$q = $_POST['q'];
$q = mb_convert_encoding( $q, "UTF-8", "GB2312,UTF8" );
if ( null==$q )
{
	echo json_encode( array( "code"=>1 ) );
	exit;
}
*/
$query_info = array(
	'query_string' => '叽歪',
	'current_page' => 1,
	'page_size' => 20, 
	'order' => true,
);

echo "<pre>";
var_dump( $query_info );

$q = base64_encode( json_encode( $query_info ) );

$query_info = json_decode( utf8_encode(base64_decode($q)), true );

$query_string = $query_info['query_string'];
$current_page = $query_info['current_page'];
$page_size = $query_info['page_size'];
$order = $query_info['order'];
$order_field = isset( $query_info['order_field'] ) ? $query_info['order_field'] : null;

var_dump( $query_string );
var_dump( $current_page );
var_dump( $page_size );
var_dump( $order );
var_dump( $order_field );

$searcher = new LuceneSearch( $user_index );

$query1 = $searcher->parseQuery( utf8_decode($query_string), "status" );
$query2 = $searcher->parseQuery( utf8_decode($query_string.'*'), "nameScreen" );
$query = $searcher->mergeShouldQuery( array($query1) );

$result = $searcher->searchKey( $query, $current_page, $page_size, $key_field, $order_field, $order );

echo "Query: $q <br/>";
echo "Count: " . $result->getResultCount() . "<br/>";
echo "<pre>";
var_dump( $result->getKeyList() );

?>
