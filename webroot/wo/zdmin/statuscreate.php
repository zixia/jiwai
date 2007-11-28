<?php
require_once( dirname(__FILE__) . '/function.php');

$m = null;
extract($_REQUEST, EXTR_IF_EXISTS);
if( null == $m ) $m = date('Y-m');

$mArray = getLastMonth();

if($m) {
	$sql = "SELECT LEFT(timeCreate,10) AS day, COUNT(1) AS count FROM Status WHERE LEFT(timeCreate,7) = '$m' GROUP BY LEFT(timeCreate,10) ORDER BY LEFT(timeCreate,10) DESC";
} else {
	$sql = "SELECT LEFT(timeCreate,10) AS day, COUNT(1) AS count FROM Status WHERE timeCreate > '$m' GROUP BY LEFT(timeCreate,10) ORDER BY LEFT(timeCreate,10) DESC";
}

$result = JWDB::GetQueryResult( $sql, true );

$render = new JWHtmlRender();
$render->display("statuscreate", array(
			'result' => $result,
			'mArray' => $mArray,
			'menu_nav' => 'statuscreate',
			));
?>
