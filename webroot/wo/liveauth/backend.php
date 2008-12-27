<?php
require_once( '../../../jiwai.inc.php' );

$consent = JWLiveAuth::Instance(JWLiveAuth::AUTH_LIVE)->ProcessRequest();
if ( $consent )
{
	JWTemplate::RedirectToUrl('/wo/invite/live');
}

JWTemplate::RedirectToUrl( '/wo/invite/' );
?>
