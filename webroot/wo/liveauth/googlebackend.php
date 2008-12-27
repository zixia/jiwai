<?php
require_once( '../../../jiwai.inc.php' );

$token = JWLiveAuth::Instance(JWLiveAuth::AUTH_GOOGLE)->ProcessRequest();
if ( $token )
{
	//JWTemplate::RedirectToUrl('/wo/invitations/invitegoogle');
	JWTemplate::RedirectToUrl('http://alpha.jiwai.de/wo/invite/google');
}

JWTemplate::RedirectToUrl( '/wo/invitations/invite' );
?>
