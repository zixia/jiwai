#!/bin/sh

while [ 1 -gt 0 ]; do
	perl ./msn-robot.pl | tee run.log
	echo "Oops! I died! :(" | tee die.log
	sleep 1
done
