<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$m = null;
extract($_REQUEST, EXTR_IF_EXISTS);
if( null == $m ) $m = date('Y-m');

$mArray = getLastMonth();

if($m) {
	$sql = "SELECT LEFT(timeCreate,10) AS day, COUNT(1) AS count FROM User WHERE LEFT(timeCreate,7) = '$m' GROUP BY LEFT(timeCreate,10) ORDER BY LEFT(timeCreate,10) DESC";
} else {
	$sql = "SELECT LEFT(timeCreate,10) AS day, COUNT(1) AS count FROM User WHERE timeCreate > '$m' GROUP BY LEFT(timeCreate,10) ORDER BY LEFT(timeCreate,10) DESC";
}

$result = JWDB::GetQueryResult( $sql, true );

$render = new JWHtmlRender();
$render->display("userregistered", array(
			'result' => $result,
			'mArray' => $mArray,
			'menu_nav' => 'userregistered',
			));
?>
