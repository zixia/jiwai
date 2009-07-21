<?php 

$log = '/tmp/bomeiti.txt';
$cb = var_export($_GET, true);

file_put_contents($log, $cb, FILE_APPEND);

if (isset($_GET['blogUrl'])) {
    $parsed = parse_url(urldecode($_GET['blogUrl']));
    if ('http' == $parsed['scheme']
        && 0 == strcasecmp($parsed['host'], 'jiwai.de')
        && empty($parsed['query'])
        && 1 == count(preg_split('/', $parsed['path'], 2, PREG_SPLIT_NO_EMPTY))) {
        die("1");
    }
}

die("0");

