<?php
require_once('../jiwai.inc.php');

$sql = "SELECT id FROM User where srcRegister='ANONYMOUS'";
$rows = JWDB::GetQueryResult($sql, true);

foreach($rows as $one)
{
	$id = $one['id'];
	$pid = 27304;
	JWSns::SetUserStatusPicture( $id, $pid );
	uSleep(300);
}       
?>
