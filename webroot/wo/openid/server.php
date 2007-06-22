<?php
require_once "../../../jiwai.inc.php";

/*
//XXX LOG
ob_start();
var_dump($_REQUEST);
$log = ob_get_contents();
file_put_contents("/tmp/openidserver.log",$log,FILE_APPEND);
ob_end_clean();
*/

// Complete the authentication process using the server's response.

$server = JWOpenidServer::GetServer();

$request = Auth_OpenID::fixArgs($_REQUEST);
$request = $server->decodeRequest($request);

if ($request) 
	JWOpenidServer::SetRequestInfo($request);
else
	$request = JWOpenidServer::GetRequestInfo();

if (!$request){
	die("no request");
}


if (in_array($request->mode,
             array('checkid_immediate', 'checkid_setup'))) 
{
	if ( !preg_match('#jiwai.de/([^/]+)#i',$request->identity,$matches) ){
		return JWOpenidServer::AuthCancel($request);
	}

	$user_name 		= $matches[1];
	$user_db_row	= JWUser::GetUserInfo($user_name);

	if ( empty($user_db_row) )
		return JWOpenidServer::AuthCancel($request);

    if (JWOpenidTrustSite::IsTrusted($user_db_row['idUser'], $request->trust_root)) 
	{
        $response =& $request->answer(true);
/*
	protected user info now.
        $sreg = JWOpenidServer::GetSregByUserId($user_db_row['idUser']);
        if (is_array($sreg)) 
		{
            foreach ($sreg as $k => $v) {
                $response->addField('sreg', $k, $v);
            }
        }
*/
    } else if ($request->immediate) {
        $response =& $request->answer(false, JWOpenidServer::GetServerURL());
    } else {
        if (!JWLogin::IsLogined()) {
			JWLogin::RedirectToLogin('/wo/openid/server');
			exit(0);
        }
		header("Location: /wo/trustsite/confirm/" . $request->trust_root);
		exit(0);
    }
} else {
    $response =& $server->handleRequest($request);
}

$webresponse =& $server->encodeResponse($response);

foreach ($webresponse->headers as $k => $v) {
    header("$k: $v");
}

header("Connection: close");
print $webresponse->body;
exit(0);

?>
