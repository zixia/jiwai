<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once( '../../jiwai.inc.php' );

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

function isWeekend($day){
	$weekday = date('N', strtotime($day));
	if( $weekday > 5 )
		return true;
	return false;
}

function getLastMonth($beginMonth='2007-04'){
	$beginTime = strtotime($beginMonth.'-01');
	$mArray = array();
	for($i=0;;$i++){

		$time = strtotime("-$i months");
		if( $time < $beginTime )
			break;

		$ms = date("Y-m", $time);
		array_push($mArray, $ms);

	}
	return $mArray;
}

function getTips(){
	$tips = isset($_SESSION['zdmin_tips']) ? $_SESSION['zdmin_tips'] : null;
	if( $tips ) {
		$_SESSION['zdmin_tips'] = null;
	}
	return $tips;
}

function setTips($string){
	$_SESSION['zdmin_tips'] = $string;
}
?>
