#!/bin/sh

while [ 1 -gt 0 ]; do
	php ./robot.php
	echo "Oops! I died! :("
	sleep 1
done
