<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400, true);
}

$authed = false;
@list($_, $type) = explode( ".", $pathParam, 2);
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

switch( $type ){
	case 'json':
		renderJsonStatuses();
	break;
	case 'xml':
		renderXmlStatuses();
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderJsonStatuses(){
    ob_start();
    ob_start("ob_gzhandler");
	$featuredWithStatus = getFeaturedWithStatus(  );
	echo json_encode( $featuredWithStatus );
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderXmlStatuses(){
    ob_start();
    ob_start("ob_gzhandler");
	$featuredWithStatus = getFeaturedWithStatus(  );

	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $featuredWithStatus, 1, "users" );
	echo $xmlString;
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function getFeaturedWithStatus()
{
	$featuredIds = JWUser::GetFeaturedUserIds(20);
	$featured = JWDB_Cache_User::GetDbRowsByIds( $featuredIds );

	$statusIds = array();
	foreach( $featuredIds as $f )
	{
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		$statusIds[$f] = $_rs['status_ids'][0];
	}
	$status_rows = JWStatus::GetDbRowsByIds( array_values($statusIds) );
	
	$featuredWithStatuses = array();
	foreach($featuredIds as $f )
	{
		$user_row = $featured[$f];
		$status_row = $status_rows[ $statusIds[$f] ];
		$user_row['idPicture'] = ( $status_row['idPicture'] && 'MMS' != $status_row['statusType'] )
			? $status_row['idPicture'] : $user_row['idPicture'];

		$userInfo = JWApi::ReBuildUser( $user_row );
		$statusInfo = JWApi::ReBuildStatus( $status_row );

		$userInfo['status'] = $statusInfo;

		$featuredWithStatuses[] = $userInfo;
	}
	return $featuredWithStatuses;
}
?>
