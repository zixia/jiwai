<?php
require_once("../../../jiwai.inc.php");

define ('SP_IP', '211.99.200.90');
define ('ZX_IP', '211.99.222.55');

$debug = false;
if ( false == $debug )
{
	$proxy_ip 	= JWRequest::GetProxyIp();
	$client_ip 	= JWRequest::GetClientIp();

	$over_ip = $proxy_ip .','. $client_ip;
	if (false === strpos($over_ip, SP_IP) && false === strpos($over_ip, ZX_IP) )
	{
		header('HTTP/1.0 401 Unauthorized');
		die ("You must use registered IP address.");
	}
}

$postedXml = isset($HTTP_RAW_POST_DATA) ?
	trim($HTTP_RAW_POST_DATA) : trim(file_get_contents("php://input"));

$message = simplexml_load_string( $postedXml );

$content = (string) $message->content;
$mobile = (string) $message->mobile;
$spcode = (string) $message->spcode;
$toicp = (string) $message->toicp;

$server_address = $spcode . $toicp;

$ret = mop_mo( $mobile, $spcode . $toicp, $content );

if ( $ret )
	echo "+OK";
else
	echo "-ERR";

exit;

function mop_mo($mobile, $server_address, $content)
{
	//$content = mb_convert_encoding($content, 'UTF-8', 'GBK,UTF-8' );
	$v = intval( JWRuntimeInfo::Get('ROBOT_COUNT_SMS_MO') );
	JWRuntimeInfo::Set( 'ROBOT_COUNT_SMS_MO', ++$v );

	JWSms::Instance();
	$robot_msg = new JWRobotMsg();
	$robot_msg->Set($mobile, 'sms', $content);
	$robot_msg->SetHeader( 'serveraddress', $server_address );
	$robot_msg->SetFile( JWSms::$msQueuePathMo . $robot_msg->GenFileName() );
	return $robot_msg->Save();
}
?>
