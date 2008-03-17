#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . "/../jiwai.inc.php");
echo date("Y-m-d H:i:s", time()) . "JWVisitUser::Update() Start\n";
JWVisitUser::Update();
echo date("Y-m-d H:i:s", time()) . "JWVisitUser::Update() Done\n";

echo date("Y-m-d H:i:s", time()) . "JWVisitTag::Update() Start\n";
JWVisitTag::Update();
echo date("Y-m-d H:i:s", time()) . "JWVisitTag::Update() Done\n";

echo date("Y-m-d H:i:s", time()) . "JWVisitThread::Update() Start\n";
JWVisitThread::Update();
echo date("Y-m-d H:i:s", time()) . "JWVisitThread::Update() Done\n";
?>
