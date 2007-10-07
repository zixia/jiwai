<?php
$u = $p = $s = null;
extract($_GET, EXTR_IF_EXISTS);

if( $u=='jiwai' && $p=='jiwaip' && $s == 'gtalk' ){
//if( true ) {
	$string = "Relogin\r\n";

	if( ! $s = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ){
		echo "-ERR 1\n";
		exit;
	}
	if( ! @socket_connect( $s, '127.0.0.1', 55010 ) ){
		echo "-ERR 2\n";
		exit;
	}

	@socket_write( $s, $string ); 
	@socket_close( $s );

	echo "+OK\n";
}

echo "-ERR 3\n";
exit;
?>
