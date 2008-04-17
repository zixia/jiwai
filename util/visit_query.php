#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . "/../jiwai.inc.php");
echo date("Y-m-d H:i:s", time()) . "JWVisitUser::Query(10) Start\n";
JWVisitUser::Query(10);
echo date("Y-m-d H:i:s", time()) . "JWVisitUser::Query(10) Done\n";

echo date("Y-m-d H:i:s", time()) . "JWVisitTag::Query(100) Start\n";
JWVisitTag::Query(100);
echo date("Y-m-d H:i:s", time()) . "JWVisitTag::Query(100) Done\n";

$types = array('normal', 'mms', 'video');
foreach($types as $type)
{
echo date("Y-m-d H:i:s", time()) . "JWVisitThread::Query('$type',10) Start\n";
JWVisitThread::Query("$type", 10);
echo date("Y-m-d H:i:s", time()) . "JWVisitThread::Query('$type',10) Done\n";
}

$devices = array('sms','fetion','qq','gtalk','msn');
foreach($devices as $device)
{
echo date("Y-m-d H:i:s", time()) . "JWStatus::GetDaRenIdsByDevice('$device',10) Start\n";
JWStatus::GetDaRenIdsByDevice("$device",10);
echo date("Y-m-d H:i:s", time()) . "JWStatus::GetDaRenIdsByDevice('$device',10) Done\n";
}

echo date("Y-m-d H:i:s", time()) . "JWStock::QueryAll() Start\n";
JWStock::QueryAll();
echo date("Y-m-d H:i:s", time()) . "JWStock::QueryAll() Done\n";
?>
