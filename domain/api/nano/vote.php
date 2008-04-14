<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
$id = null;
$type = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) 
{
	JWApi::OutHeader(400, true);	
}

$user_id = JWApi::GetAuthedUserId();
if( ! $user_id ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

@list($id, $type) = explode( ".", $pathParam, 2);
if( !in_array( $type, array('json','xml') ))
{
	JWApi::OutHeader(406, true);
}

if( false==is_numeric($id) )
{
	JWApi::OutHeader(406, true);
}

$vote_row = JWNanoVote::GetDbRowByNumber( $id );
if ( empty( $vote_row ) )
{
	JWApi::OutHeader(404, true);
}

$status_id = $vote_row['idStatus'];
$status_row = JWDB_Cache_Status::GetDbRowById($status_id);

if ( $user_id != $status_row['idUser'] )
{
	JWApi::OutHeader(406, true);
}

switch( $type )
{
	case 'json':
		renderJsonVote($vote_row);
		break;
	case 'xml':
		renderXmlVote($vote_row);
		break;
	default:
		JWApi::OutHeader(406, true);
}

function renderJsonVote($vote_row)
{
	$vote_info = genVote( $vote_row );
	echo json_encode( $vote_info );
}

function renderXmlVote($vote_row)
{
	$vote_info = genVote( $vote_row );
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $vote_info, 0, 'vote' );
	echo $xmlString;
}

function genVote($vote_row)
{
	$vote_result = JWNanoVote::DoVoteInfo( $vote_row['idStatus'] );
	$status_row = JWDB_Cache_Status::GetDbRowById($vote_row['idStatus']);
	$user_row = JWUser::GetUserInfo($status_row['idUser']);
	$vote_item = JWSns::ParseVoteItem( $status_row['status'] );

	$items = array();
	foreach( $vote_item['items'] AS $key=>$one )
	{
		$a = array(
			'text' => $one,
			'result' => @$vote_result[$key+1],
		);
		array_push( $items, $a );
	}

	$vote = array(
		'user' => JWApi::ReBuildUser($user_row),
		'status' => $vote_item['status'],
		'device' => $vote_row['deviceAllow'],
		'begin' => $vote_row['timeCreate'],
		'end' => $vote_row['timeExpire'],
		'items' => $items,
	);

	return $vote;
}
?>
