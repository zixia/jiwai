<?php
if(!defined('TPL_COMPILED_DIR')) 
	define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) 
	define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');

require_once( '/opt/jiwai.de/jiwai.inc.php' );

function SetNotice($notice=null, $refresh=false){
	if( $notice )
		$_SESSION['notice'] = $notice;

	if( $refresh ){
		Header('Location: ' . $_SERVER['REQUEST_URI'] );
		exit;
	}
}
function GetNotice($once=true){
	$notice = isset( $_SESSION['notice'] ) ? $_SESSION['notice'] : null;
	if( $once==true && isset( $_SESSION['notice'] ) ) 
		unset( $_SESSION['notice'] );

	return $notice;
}

function CmdResult($result=null){
	if( $result === null ) {
		if( isset( $_SESSION['CmdResult'] ) ){
			$result = $_SESSION['CmdResult'];
			unset( $_SESSION['CmdResult'] );
			return $result;
		}
		return null;
	}

	$_SESSION['CmdResult'] = $result;
}

JWSession::Instance();
if( false == isset( $_SESSION['stock_user'] ) && $_POST ){
	$user = $pass = null;
	if( $_POST ) {
		extract( $_POST, EXTR_IF_EXISTS );
		$fp = @fopen( dirname(dirname(__FILE__) ).'/stock_user', 'r' );
		while( $l = trim( @fgets($fp) ) ) {
			$u = $p = null;
			@list( $u, $p ) = explode( ':', $l, 2 );
			if( $u == $user && $p == $pass ) {
				$_SESSION['stock_user'] = array( 'user'=>$u, 'pass'=>md5($p), );
			}
		}
		@fclose( $fp );
	}

	Header('Location: /');
	exit;
}
if( false == isset( $_SESSION['stock_user'] ) ) {
	JWRender::Display( 'login' , array('user'=>@$user, 'pass'=>@$pass,) );
	exit;
}
?>
