<?php
require_once( './config.inc.php' );

if ( JWLogin::IsLogined() ){
    header('Location: /wo/');
    exit;
}

JWTemplate::wml_doctype();
JWTemplate::wml_head();

$htmlRender = new JWHtmlRender();
$shortcut = array('public_timeline', 'register');
$htmlRender->display( 'index', array(
                    'shortcut' => $shortcut,
                ));

JWTemplate::wml_foot();
?>
