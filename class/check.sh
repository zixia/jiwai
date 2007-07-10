#!/bin/sh

for f in `find . -type f -name "*.php"`; do
	php -l $f
done
