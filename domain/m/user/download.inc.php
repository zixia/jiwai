<?php
require_once( '../config.inc.php' );

$supportedFunc = array (
        's60',
        'widsets',
        );

$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
@list($dummy, $func, $param) = split('/', $pathParam, 3);

$func = strtolower($func);
if (!empty($func)
        && !in_array($func, $supportedFunc)) {
    redirect_to_404( '/' );
} else {
    $render = (empty($func)) ? 'download' : 'dl/'.$func;
    $shortcut = array('public_timeline', 'register');
    JWRender::display( $render, array(
                'shortcut' => $shortcut,
                ));
}
?>
