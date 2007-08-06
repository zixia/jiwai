<?php
require_once( '../config.inc.php');

$name = $pass = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

if( $name == null ) {
	header( 'Location: '.buildUrl('/') );
	exit(0);
}

$idUser = JWUser::GetUserFromPassword($name, $pass);
if ( $idUser ) {
	JWLogin::Login($idUser, false);
	header( 'Location: '.buildUrl( '/wo/' ) );
	exit(0);
}else{
    JWTemplate::wml_doctype();
    JWTemplate::wml_head();
    $render = new JWHtmlRender();
    $render->display( 'index', array(
                    'error' => '账户密码不匹配!',
                    'shortcut' => array('public_timeline', 'register'),
                    ));
    JWTemplate::wml_foot();
}
?>
