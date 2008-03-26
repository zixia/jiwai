<?php
import de.jiwai.lucene.*;
error_reporting(0);
$status_index = '/opt/lucene/index/statuss';
$key_field = 'id';
$order_field = null;

$query_info_demo = array(
	'query_string' => '网页',
	'user' => array(0=>'coven'),
//	'tag' => array('叽歪','seek','zixia'),
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

$searcher = new LuceneSearch( $status_index );

$query = $searcher->parseQuery( $query_string, "status" );

/**
 * advance search [type, device, user, tag] limitations;
 */

/**
 * search in types( type1,  type2, ... ) '
 * should clause'
 */
if ( isset($query_info['type']) )
{
	if ( count($query_info['type']) == 1 )
	{
		$one_query = $searcher->termQuery( 'Y', array_shift($query_info['type']) );
	}
	else
	{
		$one_query = array();
		foreach ( $query_info['device'] AS $one )
		{
			array_push( $one_query, $searcher->termQuery( 'Y', $one ) );
		}
		$one_query = $searcher->mergeShouldQuery( $one_query );
	}
	$query = $searcher->mergeMustQuery( array($query, $one_query) );
}

/**
 * search in users( devices1,  device2, ... ) '
 * should clause'
 */
if ( isset($query_info['device']) )
{
	if ( count($query_info['device']) == 1 )
	{
		$one_query = $searcher->termQuery( array_shift($query_info['device']), 'device');
	}
	else
	{
		$one_query = array();
		foreach ( $query_info['device'] AS $one)
		{
			array_push( $one_query, $searcher->termQuery( $one, 'device' ) );
		}
		$one_query = $searcher->mergeShouldQuery( $one_query );
	}
	$query = $searcher->mergeMustQuery( array($query, $one_query) );
}

if ( isset($query_info['device']) )
{
	$one_query = $searcher->termQuery( $query_info['device'], 'device');
	$query = $searcher->mergeMustQuery( array($query, $one_query) );
}

/**
 * search in users( user1,  user2, ... ) '
 * should clause'
 */
if ( isset($query_info['user']) )
{
	if ( count($query_info['user']) == 1 )
	{
		$one_query = $searcher->termQuery( array_shift($query_info['user']), 'user');
	}
	else
	{
		$one_query = array();
		foreach ( $query_info['user'] AS $one )
		{
			array_push( $one_query, $searcher->termQuery( $one, 'user' ) );
		}
		$one_query = $searcher->mergeShouldQuery( $one_query );
	}
	$query = $searcher->mergeMustQuery( array($query, $one_query) );
}

/**
 * search in tags( tag1,  tag2, ... ) '
 * should clause'
 */
if ( isset($query_info['tag']) )
{
	if ( count($query_info['tag']) == 1 )
	{
		$one_query = $searcher->termQuery( array_shift($query_info['tag']), 'tag');
	}
	else
	{
		$one_query = array();
		foreach ( $query_info['tag'] AS $one)
		{
			array_push( $one_query, $searcher->termQuery( $one, 'tag' ) );
		}
		$one_query = $searcher->mergeShouldQuery( $one_query );
	}
	$query = $searcher->mergeMustQuery( array($query, $one_query) );
}
// end advance


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
