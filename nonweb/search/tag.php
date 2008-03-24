<?php
import de.jiwai.lucene.*;
error_reporting(0);
$tag_index = '/opt/lucene/index/tags';
$key_field = 'id';
$order_field = null;

$query_info_demo = array(
	'query_string' => '笑话',
	'current_page' => 1,
	'page_size' => 20, 
	'order' => true,
	'demo' => true,
);

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : base64_encode( json_encode( $query_info_demo ) );

$query_info = json_decode( base64_decode($q), true );

$query_string = $query_info['query_string'];
$current_page = $query_info['current_page'];
$page_size = $query_info['page_size'];
$order = $query_info['order'];
$order_field = isset( $query_info['order_field'] ) ? $query_info['order_field'] : null;
$demo = isset($query_info['demo']) ? $query_info['demo'] : false;

if ( $demo )
{
	$query_info_demo['demo'] = false;
	echo "<pre>";
	echo "?q=".base64_encode( json_encode($query_info_demo) )."<br/>";
	var_dump( $query_info_demo );
}

$searcher = new LuceneSearch( $tag_index );

$query1 = $searcher->parseQuery( $query_string, 'name' );
$query2 = $searcher->parseQuery( $query_string, 'description' );
$query = array($query1, $query2);

$query = $searcher->mergeShouldQuery( $query );

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
