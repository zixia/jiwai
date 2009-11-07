<?php
require_once('../../../jiwai.inc.php');

$p = 0; $s = 1000;
$v = array();
while(true) {
	$limit = $p * $s;
	$q = "SELECT * FROM Follower limit $limit,$s";
	$r = JWDB::GetQueryResult($q, true);
	if (!$r) break;
	foreach($r AS $one) {
		$idUser = $one['idUser'];
		$idFollower = $one['idFollower'];
		echo "$idUser\t$idFollower\n";
	}
	$p++;

}
