<?php
if(!defined('TPL_COMPILED_DIR')) 
	define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) 
	define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');

require_once( '/opt/jiwai.de/jiwai.inc.php' );

function checkUser(){
	global $in_login_page;
	if ( $in_login_page ) 
		return true;

	$idUser = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : null;
	if ( $idUser ) 
		return true;

	JWTemplate::RedirectToUrl( '/login.php' );
}
checkUser();

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
?>
