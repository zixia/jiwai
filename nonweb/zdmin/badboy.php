<?php
require_once( dirname(__FILE__) . '/function.php');

$w = $r = null;
extract($_POST, EXTR_IF_EXISTS);

$dictFileName = FRAGMENT_ROOT . '/page/badboy.txt';
$dictFileNameDone = FRAGMENT_ROOT . '/page/badboy_done.txt';

if( $w ) {
	$w = mb_convert_encoding($w, "GB2312", "UTF-8");
	file_put_contents($dictFileName, $w );
}

if( $r ) {
	$r = mb_convert_encoding($r, "GB2312", "UTF-8");
	file_put_contents($dictFileNameDone, $r );
}
if ( $w || $r ) {
	Header("Location: badboy.php");
	exit;
}

$fr = file_get_contents( $dictFileName );
$fr = mb_convert_encoding($fr, "UTF-8", "GB2312");

$rr = file_get_contents( $dictFileNameDone );
$rr = mb_convert_encoding($rr, "UTF-8", "GB2312");

$render = new JWHtmlRender();
$render->display("badboy", array(
			'fresult' => $fr,
			'rresult' => $rr,
			'menu_nav' => 'badboy',
			));
?>
