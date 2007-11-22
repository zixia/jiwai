<?php
function checkUser(){
	$validUserIds = array(1,4,11,89,863,20,2802,32834);
	$idUser = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : null;
	if( $idUser && in_array( $idUser, $validUserIds ) ){
		return true;
	}
	Header("Location: http://jiwai.de/");
	exit;
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
