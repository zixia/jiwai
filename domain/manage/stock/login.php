<?php
$user = $pass = null;
if( $_POST ) {
	extract( $_POST, EXTR_IF_EXISTS );
	$fp = @fopen( dirname(dirname(__FILE__) ).'/stock_user', 'r' );
	var_Dump( $fp );
	while( $l = trim( @fgets($fp) ) ) {
		$u = $p = null;
		@list( $u, $p ) = explode( ':', $l, 2 );
		if( $u == $user && $p == $pass ) {
			$_SESSION['stock_user'] = array( 'user'=>$u, 'pass'=>md5($p), );
		}
	}
	@fclose( $fp );
}

if( isset( $_SESSION['stock_user'] ) ) {
	Header('Location: /');
	exit;
}

require_once( dirname(__FILE__).'/config.inc.php' );
?>
