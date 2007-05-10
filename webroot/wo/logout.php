<?php
require_once('../../jiwai.inc.php');

JWLogin::Logout();
header("Location: /");
exit(0);

?>
