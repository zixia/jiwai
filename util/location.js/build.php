<?php
if(!defined('TPL_COMPILED_DIR'))
    define('TPL_COMPILED_DIR', dirname(__FILE__) );
if(!defined('TPL_TEMPLATE_DIR'))
    define('TPL_TEMPLATE_DIR', dirname(__FILE__) );

require_once( '/opt/jiwai.de/jiwai.inc.php' );

$rows = JWLocation::GetDbRowsByIdParent( 0 );

$rtn = array();
foreach( $rows as $r ) {
    $rs = JWLocation::GetDbRowsByIdParent( $r['id'] );
    $rtn[ $r['id'] ] = array(
        $r['name'],
        array(),
    );

    foreach( $rs as $rsr ){
        $c = array( $rsr['id'], $rsr['name'] );
        array_push( $rtn[ $r['id'] ] [1], $c );
    }
}

$p = json_encode( $rtn );

$r = JWRender::Render( 'location', array( 'v' => $p ) );
file_put_contents( '/opt/jiwai.de/domain/asset/js/location.js', $r );
?>
