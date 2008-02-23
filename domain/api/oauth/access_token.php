<?php
include_once '../../../jiwai.inc.php';
$server = JWOAuth::Server();
try {
  $req = OAuthRequest::from_request();
  $token = $server->fetch_access_token($req);
  print $token;
} catch (OAuthException $e) {
  print($e->getMessage() . "\n<hr />\n");
  print_r($req);
  die();
}
?>
