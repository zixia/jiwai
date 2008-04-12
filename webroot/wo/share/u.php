<?php
require_once('../../../jiwai.inc.php');
define( 'BOOKMARKLET_IDPARTNER', 10037 );
$idUser = JWLogin::GetPossibleUserId();
if( false == $idUser ) {
	Header('Location: /wo/share/login');
	exit;
}

if( $_POST ) {

	$idPartner = $url = $title = $description = null;
	extract( $_POST );
	$status = "$title - $description $url";
	if( $idPartner == null )
		$idPartner = BOOKMARKLET_IDPARTNER;

	if( strpos( strtolower($url), 'http://jiwai.de/' ) === 0 ) {
		Header( "Location: /" );
		exit;
	}

	$device = 'api';
	$timeCreate = date("Y-m-d H:i:s");
	$status = urlDecode( $status );
	$serverAddress = null;
	$options = array(
		'idPartner' => $idPartner,
	);

	$idStatus = JWSns::UpdateStatus($idUser, $status, $device, $time=null, $serverAddress, $options);
	if( $idStatus > 1 ) JWFavourite::Create($idUser, $idStatus);
	
	exit;
}
?>
