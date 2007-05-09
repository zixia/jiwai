#!/usr/bin/php
<?php
define ('CONSOLE', true);

require_once(dirname(__FILE__) . "/../../jiwai.inc.php");


/*
function test () {
	echo "okok!\n";
}

$items = array (	'item1'		=> array (	'identifier'	=> 1
											, 'text'		=> 'item1 text'
											, 'callback'	=> 'die'
										)
					, 'item2'	=> array (	'identifier'	=> 2
											, 'text'		=> 'item2 text'
											, 'callback'	=> 'test'
										)
				);

JWConsole::menu ($items, true);
*/

//echo JWConsole::convert("%yzixia");

JWRobot::run();
//?>
