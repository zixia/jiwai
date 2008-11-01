#!/usr/bin/php
<?php
$count = trim(`ps aux | grep 'online_mo.php' | grep -v grep | wc -l`);
if( $count == 7 ) {
	exit(0);
}
echo "online_mo.php miss Java process call!";
exit(1)
?>
