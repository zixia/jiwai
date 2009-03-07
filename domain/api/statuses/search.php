<?php
require_once("../../../jiwai.inc.php");
$callback = null;
$count = 20;
$page = 1;
$thumb = 48;
$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
$page = ( $page < 1 ) ? 1 : intval($page);

if( false == preg_match( '/(.*)\.([[:alpha:]]+)$/', trim($pathParam,'/'), $matches ) ){
	JWApi::OutHeader(406,true);
}

@list($q, $type) = array($matches[1], $matches[2]);
$_GET['q'] = $q;

if( false == in_array($type, array('xml','json','atom','rss'))){
	JWApi::OutHeader(406, true);
}

$extra = array(
	'order_field' => 'time',
	'order' => true,
);

$result = JWSearch::SearchStatus($q, $page, $count, $extra);
$statuses = JWDB_Cache_Status::GetDbRowsByIds( $result['list'] );
foreach( $statuses AS $k=>$one ) {
	$statuses[$k]['status'] = JWUtility::HighLight($one['status'], '<b>', '</b>');
}

$options = array(
		'thumb' => $thumb,
		'callback' => $callback,
		'statuses' => $statuses,
		'page' => $page,
		'q' => $q,
		);

switch($type){
	case 'xml':
		renderXmlReturn($options);
	break;
	case 'json':
		renderJsonReturn($options);
	break;
	case 'atom':
		renderFeedReturn($options, JWFeed::ATOM);
	break;
	case 'rss':
		renderFeedReturn($options, JWFeed::RSS20);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($options){
    ob_start();
    ob_start("ob_gzhandler");

	$statuses = $options['statuses'];
	$q = $options['q'];
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($statuses, 0, 'statuses');

	echo $xmlString;
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderJsonReturn($options){
    ob_start();
    ob_start("ob_gzhandler");

	$statuses = $options['statuses'];
	$q = $options['q'];

	if( $options['callback'] ){
		echo $options['callback'].'('. json_encode($statuses) .')';
	}else{
		echo json_encode($statuses);
	}
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderFeedReturn($options, $feedType=JWFeed::ATOM){
    ob_start();
    ob_start("ob_gzhandler");

	$statuses = $options['statuses'];
	$q = $options['q'];

	$feed = new JWFeed(array(
				'title'	=> '叽歪搜索 / '. $q,
				'url'	=> 'http://JiWai.de/k/'.$q .'/', 
				'desc'	=> '词汇{' .$q. '}' , 
				'ttl'	=> 40,
				)); 

	foreach ( $statuses as $status ){

		$feed->AddItem(array( 
					'title'	=> JWApi::RemoveInvalidChar($status['status']) , 
					'desc'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
					'date'	=> strtotime( $status['timeCreate'] ), 
					'author'=> $status['user']['nameScreen'] , 
					'guid'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'] , 
					'url'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'],
					));
	}
	$feed->OutputFeed($feedType);
	ob_end_flush();
	header('Content-Length: '.ob_get_length());
	ob_end_flush();
}
?>
