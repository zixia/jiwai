<?php
require_once( '/opt/jiwai.de/jiwai.inc.php' );
$prow = array(
	'nameScreen' => '和信',
	'nameDevice' => '和信',
	'remark' => '联系人：www.hesine.com',
	'email' => 'lvjlcn@126.com',
	'link' => 'http://www.hesine.com/',
	'timeCreate' => date('Y-m-d H:i:s'),
);
 
echo JWPartner::Create( $prow );
?>
