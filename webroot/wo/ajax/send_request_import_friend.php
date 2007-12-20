<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

$type = $username = $password = null;
extract( $_POST, EXTR_IF_EXISTS );

JWBuddy_Robot::SendImportRequest($type, $username, $password);
?>
