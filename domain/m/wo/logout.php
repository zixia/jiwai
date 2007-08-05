<?php
require_once( '../config.inc.php' );
JWLogin::Logout();
header("Location: /");
exit(0);
?>
