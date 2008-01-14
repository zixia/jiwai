<?php
require_once( './config.inc.php' );

$tag_ids = JWComStockAccount::GetIdUsersByType( JWComStockAccount::T_STOCK );

if( empty( $tag_ids ) )
{
    SetNotice( "系统中还没有登记股票账户" );
}

$tag_rows = JWTag::GetDbRowsByIds( $tag_ids );
JWRender::Display('stock',array(
    'tag_rows'  =>  $tag_rows,
));
?>
