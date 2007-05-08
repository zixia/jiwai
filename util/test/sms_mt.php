#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . "/../../jiwai.inc.php");

while ($line=JWConsole::getline()){
	// get and use remaining arguments

	if ( ! preg_match('/^(\d+)\s+(.+)$/', $line, $matches) )
	{
		echo "ERR parse\n";
		continue;
	}

	$mobileNo = $matches[1]; 
	$smsMsg = urldecode($matches[2]);

	if ( empty($mobileNo) || empty($smsMsg) )
	{
		echo "ERR need param\n";
		continue;
	}

	//echo "$mobileNo [$smsMsg]\n";


	if ( ! JWSms::SendMt($mobileNo, $smsMsg) )
//	if ( ! JWSms::SendMt('13911833788', 'test') )
	{
		echo "ERR MT\n";
	}
	else
	{
		echo "OK\n";
	}
}
?>
