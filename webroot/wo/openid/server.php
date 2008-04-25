<?php
require_once(dirname(__FILE__) . "/../../../jiwai.inc.php");
function getOpenIDStore()
{
    return JWOpenID::GetStore();
}
/*
$path_extra = dirname(dirname(dirname(__FILE__)));
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);
*/

JWOpenID::GetStore();//FIXME

header('Cache-Control: no-cache');
header('Pragma: no-cache');

require_once 'lib/session.php';
require_once 'lib/actions.php';

$action = getAction();
if (!function_exists($action)) {
    $action = 'action_default';
}

$resp = $action();

writeResponse($resp);
?>
