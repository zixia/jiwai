<?php
require_once( dirname(dirname(dirname(__FILE__))).'/jiwai.inc.php' );

$message = '国庆节到了，叽歪向你问好，祝你：节日期间吃好！喝好！玩好！出行旅游时带上相机，记录美好时刻，开心的同时别忘了注意安全！随时随地叽歪一下～';

JWRuntimeInfo::Set( 'JIWAI_NUDGE_SMS_GUOQING', $message );

var_dump(
JWRuntimeInfo::Get( 'JIWAI_NUDGE_SMS_GUOQING' )
);

?>
