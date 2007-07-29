#!/usr/bin/php
<?php
define ('CONSOLE', true);

require_once(dirname(__FILE__) . "/../../jiwai.inc.php");

JWStatusNotifyQueue::Run();
?>
