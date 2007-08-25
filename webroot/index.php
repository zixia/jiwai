<?php
require_once('../jiwai.inc.php');

if ( JWLogin::IsLogined() )
	header('Location: /wo/');

require_once(dirname(__FILE__) . '/user/public_timeline.inc.php');
?>
