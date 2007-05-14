#!/bin/sh

while [ 1 -gt 0 ]; do
	php ./jiwai-robot.php
	echo "Oops! I died! :("
	sleep 1
done
