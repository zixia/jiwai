<?php
require_once('../../../jiwai.inc.php');
$user_id = JWLogin::GetPossibleUserId();
JWLogin::Login( $user_id, true );
echo '+' . $user_id;
exit(0);
?>
