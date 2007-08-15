#!/usr/bin/php
<?php
define ('CONSOLE', true);

require_once(dirname(__FILE__) . "/../../jiwai.inc.php");

while ($line=JWConsole::getline())
{
	// get and use remaining arguments

	if ( ! preg_match('/^(\d+)\s+(\S+)\s+(\d+)\s*(\d*)$/', $line, $matches) )
	{
		$line = preg_replace('[\r\n]','',$line);
		echo "ERR parse [$line]\n";
		continue;
	}

	$mobile_no 		= $matches[1]; 
	$sms_msg 		= urldecode($matches[2]);
	$server_address	= $matches[3];

	if ( empty($matches[4]) )
		$link_id = null;
	else
		$link_id= $matches[4];

	if ( empty($mobile_no) || empty($sms_msg) || empty($server_address) )
	{
		echo "ERR need param\n";
		continue;
	}

	// debug
	//echo "$mobile_no [$sms_msg] from $server_address by $link_id\n";
	//continue;

	if ( ! JWSms::SendMt($mobile_no, $sms_msg, $server_address, $link_id) )
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
