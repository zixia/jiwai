<?php
require_once '../../jiwai.inc.php';

function pushRecvQueue($user, $text, $time) {
	echo "Got from $user: $text\n";
}

function checkSendQueue() {
	$a = array();
	$a[] = array('user'=>'FreeWizard', 'text'=>'赫赫 test');
	return $a;
}

require_once 'KBS/Client.php';

$c = new KBS_Client('http://www.newsmth.net', 'JiWai', '123789');
while (!$c->loggedin) {
	echo "Login failed, wait and retry\n";
	sleep(15);
	$c->login();
}
echo "Logged in.\n";

while (1) {
	sleep(15);
	$r = $c->receiveMessage();
	if ($r) foreach($r as $m) pushRecvQueue($m['user'], $m['text'], $m['time']);
	sleep(15);
	$r = checkSendQueue();
	if ($r) foreach($r as $m) $c->sendMessage($m['user'], $m['text']);
}

?>
