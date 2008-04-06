<?php
require_once( '../../../jiwai.inc.php' );

$consent = JWLiveAuth::ProcessRequest();
if ( $consent )
{
	JWTemplate::RedirectToUrl('/wo/invitations/invitelive');
}

JWTemplate::RedirectToUrl( '/wo/invitations/invite' );
?>
