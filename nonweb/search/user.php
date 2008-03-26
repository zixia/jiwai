<?php
import de.jiwai.lucene.*;
error_reporting(0);
$user_index = '/opt/lucene/index/users';
$key_field = 'id';
$order_field = null;

$query_info_demo = array(
	'query_string' => '精华',
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

$searcher = new LuceneSearch( $user_index );

$query1 = $searcher->parseQuery( $query_string, 'bio' );
$query2 = $searcher->parseQuery( $query_string.'*', 'nameScreen' );
$query3 = $searcher->termQuery( $query_string, 'nameFull' );
$query = array($query1, $query2, $query3);

// search from devices 
if( preg_match('/@/', $query_string) || is_numeric($query_string) )
{
	$query4 = $searcher->parseQuery( $query_string, 'devices');
	$query = array( $query1, $query2, $query3, $query4 );
}
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
