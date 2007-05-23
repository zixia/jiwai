<?php
require_once '../../jiwai.inc.php';

/*
 *	MO: Mobile Origined		用户发出的消息
 *	MT: Mobile Terminated. 	在这里代表以用户为终点的消息
 *
 */

$config 	= JWConfig::Instance();
$directory	= $config->directory;

$path_mo	= $directory->queue->root 
				. $directory->queue->newsmth
				. $directory->mo
			;

$path_mt	= $directory->queue->root 
				. $directory->queue->newsmth
				. $directory->mt
			;


//die ( "mo: $path_mo mt: $path_mt");

function pushRecvQueue($user, $text, $time) {
	global $path_mo;

	echo "Got from $user: $text\n";

	$address 	= "$user@newsmth.net";
	$type		= "newsmth";

	$robot_msg = new JWRobotMsg();
	$robot_msg->Set( $address, $type, $text );
	$robot_msg->SetFile( $path_mo . $robot_msg->GenFileName() );
	$robot_msg->Save();
}

function checkSendQueue() {
	global $path_mt;

	$handle=opendir($path_mt);

	$counter = 0;

	if ( !$handle ){
		JWLog::Instance()->Log(LOG_ERR, "mo opendir [$path_mt] failure.");
		throw new JWException ( "JWRobot opendir[$path_mt] failure" );
	}

	$robot_msgs 	= array();
	$return_num_max = 100;

	while ( false !== ($file=readdir($handle)) ) {
		// must like "msn__*" or "sms__*", etc.
		if ( ! preg_match('/^\w+__/', $file) )
			continue;

		$file = $path_mt . $file;

		$robot_msg = new JWRobotMsg($file);

		/*
		 * 只需要一个，返回。
		 */
		array_unshift ($robot_msgs, $robot_msg);

		if ( $counter++ >= $return_num_max )
			break;
	}

	closedir($handle);

	return $robot_msgs;
}

require_once 'KBS/Client.php';

$c = new KBS_Client('http://www4.newsmth.net', 'JiWai', '123789');
while (!$c->loggedin) {
	echo "Login failed, wait and retry\n";
	sleep(15);
	$c->login();
}
echo "Logged in.\n";

$idle_circle = 0; 				// default 0 ms, work hard.;
$idle_circle_max = 1000 * 15; 	// 15 s

while (1) {
	print "."; // means a loop

	$r = $c->receiveMessage();
	if ($r){
		$idle_circle = 0; // maybe other new msg! goto work.
		 foreach($r as $m) pushRecvQueue($m['user'], $m['text'], $m['time']);
	}

	$robot_msgs = checkSendQueue();

	if ( count($robot_msgs) ) {
		$idle_circle = 0; // maybe other new msg! goto work.
		foreach($robot_msgs as $robot_msg) {
			$address 	= $robot_msg->GetAddress();
			$body		= $robot_msg->GetBody();

			if ( ! preg_match('#^(\w+)@newsmth.net$#',$address,$matches) ) {
				JWLog::LogFuncName(LOG_CRIT, "kbs robot received a address from [$address] with body [$body] which is unknown.");
				continue;
			} 

			$newsmth_user = $matches[1];

			$c->sendMessage( $newsmth_user, $body );
			$robot_msg->Destroy();
		}
	}

print "$idle_circle \n";
	// 根据刚刚是否有任务，决定睡多久
	if ( 0==$idle_circle ) {
		$idle_circle++;
	} else {
		$idle_circle *= 4; // 对水木宽容一些

		if ( $idle_circle > $idle_circle_max )
			$idle_circle = $idle_circle_max;

		usleep($idle_circle * 1000);
	}
}

?>
