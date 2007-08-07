<?php
require_once( './config.inc.php' );

if ( JWLogin::IsLogined() ){
    header('Location: '.buildUrl('/wo/') );
    exit;
}

$shortcut = array('public_timeline', 'register');
JWRender::display( 'index', array(
                    'shortcut' => $shortcut,
                ));
?>
