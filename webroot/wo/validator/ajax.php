<?php
require_once( '../../../jiwai.inc.php' );
$k = $v = $v2 = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

$result = JWFormValidate::Validate($k, $v, $v2);
if( true !== $result )
{
	echo $result;
}
?>
