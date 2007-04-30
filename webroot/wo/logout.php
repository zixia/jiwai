<?php
require_once('../../jiwai.inc.php');

JWUser::Logout();
header("Location: /");
exit(0);

?>
