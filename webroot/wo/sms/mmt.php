<?php
include '../../../jiwai.inc.php' ;

$url = "http://sms.jiwai.de/wo/sms/mom";

$url = "http://211.157.106.172:8000/mms/submit";

$img = base64_encode( file_get_contents("1.gif") );

$data = <<<DATA
<mmsMT>
	<AppID>12</AppID>
	<GatewayID>1</GatewayID>
	<Receiver><to>13955457592</to></Receiver>
	<Subject>This is CX</Subject>
	<ProductID>0</ProductID>
	<MMS>
	      <content>
		    <smil encode="base64">abc</smil>
		    <item encode="1.txt"></item>
	      </content>
	</MMS>
</mmsMT> 
DATA;


var_dump( simplexml_load_string( $data ) );

//echo JWNetFunc::DoPost( $url, $data ) ;

?>
