<?php
require_once('../../jiwai.inc.php');

JWUser::logout();
header("Location: /");
exit(0);

?>
