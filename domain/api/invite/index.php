<?php
require_once("../../../jiwai.inc.php");
$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

if( false == preg_match( '/(.*)\.([[:alpha:]]+)$/', trim($pathParam,'/'), $matches ) ){
	JWApi::OutHeader(406,true);
}

$phone = $message = null;
extract($_POST, EXTR_IF_EXISTS);

$idUser = null;
if( false == ( $idUser=JWApi::GetAuthedUserId() ) ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$message .= ' 回复 F 确定。';

$user = JWUser::GetUserInfo( $idUser );
if( !$user ){
	JWApi::OutHeader(404, true);
}
$idUser = $user['id'];

if( JWSPCode::GetSupplierByMobileNo($phone ) && JWSns::SmsInvite( $idUser, $phone, $message ) ){
	echo '+OK';	
}else{
	echo '-ERR';
}
?>
