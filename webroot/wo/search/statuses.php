<?php

$g_search = true;
$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : null;
$eq = $q;

$in_user = "*";
if (preg_match("/\s+in\s*:\s*([\w_\-\.]+)\s*?/", $q, $matches) ){
	$in_user = $matches[1];
	$eq = preg_replace("/\s+in\s*:\s*([\w_\-\.]+)/","",$q);
	$q = preg_replace("/\s+in\s*:\s*([\w_\-\.]+)/"," in:$in_user",$q);
}

include("../index.php");
?>
