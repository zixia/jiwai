<?php
import de.jiwai.lucene.*;
error_reporting(0);
$status_index = '/opt/lucene/index/statuss';
$key_field = 'id';
$order_field = null;

$query_info = array(
	'query_string' => '叽歪',
	'current_page' => 1,
	'page_size' => 20, 
	'order' => true,
	'demo' => true,
);

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : base64_encode( json_encode( $query_info ) );

$query_info = json_decode( utf8_encode(base64_decode($q)), true );

$query_string = $query_info['query_string'];
$current_page = $query_info['current_page'];
$page_size = $query_info['page_size'];
$order = $query_info['order'];
$order_field = isset( $query_info['order_field'] ) ? $query_info['order_field'] : null;
$demo = isset($query_info['demo']) ? $query_info['demo'] : false;

if ( $demo )
{
	$query_info['demo'] = false;
	echo "<pre>";
	echo "?q=".base64_encode( json_encode($query_info) )."<br/>";
	var_dump( $query_info );
}

$searcher = new LuceneSearch( $status_index );

$query = $searcher->parseQuery( utf8_decode($query_string), "status" );

try{
	$result = $searcher->searchKey( $query, $current_page, $page_size, $key_field, $order_field, $order );
}catch(Exception $e){
	die('{"error":1}');
}

$return = array(
	'error' => 0,
	'count' => $result->getResultCount(),
	'list' => $result->getKeyList(),
);

echo json_encode( $return );
exit;
?>
