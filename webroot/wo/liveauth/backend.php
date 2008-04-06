<?php
require_once( '../../../jiwai.inc.php' );

$consent = JWLiveAuth::Instance(JWLiveAuth::AUTH_LIVE)->ProcessRequest();
if ( $consent )
{
	JWTemplate::RedirectToUrl('/wo/invitations/invitelive');
}

JWTemplate::RedirectToUrl( '/wo/invitations/invite' );
?>
