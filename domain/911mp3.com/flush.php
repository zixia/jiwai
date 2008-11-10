<?php
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");
$memcache->flush();
?>

