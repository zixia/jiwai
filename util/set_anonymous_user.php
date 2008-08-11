<?php
require_once('../jiwai.inc.php');

$sql = "SELECT id FROM User where srcRegister='ANONYMOUS'";
$rows = JWDB::GetQueryResult($sql, true);

foreach($rows as $one)
{
	$id = $one['id'];
	$up = array(
		'bio' => '这是一个IP漂流瓶用户。他是由很多匿名用户组成的，因为他们都有着共同的IP段，于是便汇聚在了一起，你看到的是这个瓶子里所有人的叽歪。',
	);
	$u = JWDB_Cache::UpdateTableRow( 'User', $id, $up );
	echo $id . $u . "\n";
	uSleep(300);
}       
?>
