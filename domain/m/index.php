<?php
require_once( './config.inc.php' );

if ( JWLogin::IsLogined() ){
    header('Location: /wo/');
    exit;
}

JWTemplate::wml_doctype();
JWTemplate::wml_head();

$htmlRender = new JWHtmlRender();
$htmlRender->display( 'index' );

JWTemplate::wml_foot();
?>
