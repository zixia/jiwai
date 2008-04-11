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

@list($id, $type) = explode( ".", $pathParam, 2);
if( !in_array( $type, array('json','xml') ))
{
	JWApi::OutHeader(406, true);
}

if( is_numeric($id) )
{
	switch( $type )
	{
		case 'json':
			renderJsonStatuses($id);
			break;
		case 'xml':
			renderXmlStatuses($id);
			break;
		default:
			JWApi::OutHeader(406, true);
	}
}
else
{
	JWApi::OutHeader(406, true);
}

function renderJsonStatuses($id)
{
	$status = $user = null;
	if( getMessage( $id, $status, $user ))
	{
		$userInfo = JWApi::ReBuildUser($user);
		$statusInfo = JWApi::ReBuildStatus($status);
		$statusInfo['user'] = $userInfo;
		echo json_encode( $statusInfo );
	}
}

function renderXmlStatuses($id)
{
	$status = $user = $xmlString = null;
	if( getMessage( $id, $status, $user ))
	{
		$userInfo = JWApi::ReBuildUser($user);
		$statusInfo = JWApi::ReBuildStatus($status);
		$statusInfo['user'] = $userInfo;

		header('Content-Type: application/xml; charset=utf-8');
		$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xmlString .= "<status>\n";
		$xmlString .= JWApi::ArrayToXml( $statusInfo, 1 );
		$xmlString .= "</status>\n";
		echo $xmlString;
	}
}

function getMessage($id, &$status, &$user)
{
	$status = JWDB_Cache_Status::GetDbRowById($id);

    $current_user_id = JWApi::GetAuthedUserId();
    if (JWSns::IsProtectedStatus($status, $current_user_id))
        return false;

	if( $status )
	{
		$user = JWDB_Cache_User::GetDbRowById($status['idUser']);
		if ( false==empty($user) )
		{
			if ( $status['idPicture'] && $status['statusType']=='MMS' )
			{
				$user['idPicture'] = $status['idPicture'];
			}
		}
		return true;
	}
	return false;
}
?>
