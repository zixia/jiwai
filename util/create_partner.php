<?php
require_once( '/opt/jiwai.de/jiwai.inc.php' );
$prow = array(
	'nameScreen' => 'JiWaiShare',
	'nameDevice' => '叽歪分享',
	'remark' => '联系人：shwdai@gmail.com',
	'email' => 'shwdai@gmail.com',
	'link' => 'http://jiwai.de/wo/share/',
	'timeCreate' => date('Y-m-d H:i:s'),
);
 
echo JWPartner::Create( $prow );
?>
