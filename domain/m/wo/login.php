<?php
require_once( '../config.inc.php');

$name = $pass = $remember = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

if( $name == null ) {
	header( 'Location: '.buildUrl('/') );
	exit(0);
}

$idUser = JWUser::GetUserFromPassword($name, $pass);
if ( $idUser ) {
	JWLogin::Login( $idUser, (null!=$remember) );
	header( 'Location: '.buildUrl( '/wo/' ) );
	exit(0);
}else{
    JWSession::SetInfo('error', '登录失败：账户名密码不匹配');
	header( 'Location: /' );
}
?>
