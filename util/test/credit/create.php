<?php
require_once( '/opt/jiwai.de/jiwai.inc.php' );

echo JWCredit::Create( 89, JWCredit::CREDIT_ADMIN );

var_dump( JWCredit::GetDbRowByIdUser( 89 ) );
?>
