<?php
require_once( './config.inc.php' );
$user = $_SESSION['stock_user']['user'];

if( $_POST )
{

    $stock_num = isset( $_POST['stock_num'] ) ? $_POST['stock_num'] : NULL;
    $tag_id = JWTag::GetIdByDescription( $stock_num );
    
    if( false == $tag_id )
    {
        SetNotice("该股票 $stock_num 没有登记。", true);
    }
    $tag_info = JWTag::GetDbRowById( $tag_id );


    if( $tag_info['admin'] == $user )
    {
        header("Location: /meeting.php?stock_num=$stock_num");
        exit(0);
    }
    else
    {
        SetNotice("你不是该股票的管理者。", true);
    }
}

JWRender::display( 'setconference', array() );
?>
