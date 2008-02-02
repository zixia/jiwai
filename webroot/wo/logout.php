<?php
require_once('../../jiwai.inc.php');

$_SESSION['logout_redirect_url'] = $_SERVER['HTTP_REFERER'];
JWLogin::Logout();
JWTemplate::RedirectBackToLastUrl();
?>
