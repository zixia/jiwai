<?php
require_once( '/opt/jiwai.de/jiwai.inc.php' );
$prow = array(
	'nameScreen' => 'iJiWai',
	'nameDevice' => 'iJiWai',
	'remark' => '联系人：jiwai.de/tsing',
	'email' => 'shwdai@gmail.com',
	'link' => 'http://labs.geowhy.org/ijiwai/',
	'timeCreate' => date('Y-m-d H:i:s'),
);
 
echo JWPartner::Create( $prow );
?>
