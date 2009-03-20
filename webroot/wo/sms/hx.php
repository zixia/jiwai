<?php
require_once("../../../jiwai.inc.php");

define ('SP_IP', '211.99.200.90');
define ('ZX_IP', '211.99.222.55');

$debug = true;
if ( false == $debug )
{
	$proxy_ip 	= JWRequest::GetProxyIp();
	$client_ip 	= JWRequest::GetClientIp();

	$over_ip = $proxy_ip .','. $client_ip;
	if (false === strpos($over_ip, SP_IP) && false === strpos($over_ip, ZX_IP) )
	{
    JWApi::OutHeader(401);
	}
}

function onSuccess() {
  die(strftime("OK_%Y-%m-%d %H:%M:%S"));
}

function onFailure() {
  JWApi::OutHeader(400);
}

$log = '/tmp/hxsms.log';
$parameters = var_export($_GET, true);
file_put_contents($log, $parameters . "\n", FILE_APPEND);

onSuccess();
