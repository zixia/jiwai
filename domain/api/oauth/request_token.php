<?php
include_once '../../../jiwai.inc.php';
$server = JWOAuth::Server();
try {
  $req = OAuthRequest::from_request();
  $token = $server->fetch_request_token($req);
  print $token;
} catch (OAuthException $e) {
  header('HTTP/1.1 400 '.$e->getMessage());
  print $e->getMessage() . "\n<hr />\n";
  //print_r($req);
}
?>
