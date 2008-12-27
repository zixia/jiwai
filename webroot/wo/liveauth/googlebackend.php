<?php
require_once( '../../../jiwai.inc.php' );

$token = JWLiveAuth::Instance(JWLiveAuth::AUTH_GOOGLE)->ProcessRequest();
if ( $token )
{
	JWTemplate::RedirectToUrl('/wo/invite/google');
}

JWTemplate::RedirectToUrl( '/wo/invite/' );
?>
