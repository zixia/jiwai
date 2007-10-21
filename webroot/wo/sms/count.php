<?php
require_once("../../../jiwai.inc.php");

$mo = JWRuntimeInfo::Get( 'ROBOT_COUNT_SMS_MO' );
$mt = JWRuntimeInfo::Get( 'ROBOT_COUNT_SMS_MT' );

echo "mo:$mo mt:$mt";
?>
