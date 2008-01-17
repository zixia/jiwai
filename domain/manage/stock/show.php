<?php
require_once('./config.inc.php');
if( $_POST )
{
    $stock_num = isset( $_POST['stock_num'] ) ? $_POST['stock_num'] : NULL;
    $tag_id = JWTag::GetIdByDescription( $stock_num );
    if( false == preg_match( '/^(\d{3}|\d{6})$/', $stock_num ) ){
        SetNotice( "SHOW 后第一个参数只能是：3位数字的分类号、6位数字的股票代码。", true);
    }
    if( false == $tag_id )
    {
        SetNotice("系统中未登记此股票账户",true);
    }
    else
    {
        $tag_rows = JWTag::GetDbRowById( $tag_id );
        $count_post = JWDB_Cache_Status::GetCountPostByIdTag( $tag_id );
        $stock_rows = JWComStockAccount::GetDbRowByIdTag( $tag_id );
        $follower_num = JWTagFollower::GetFollowerNum( $tag_id );
        $rows = array_merge($tag_rows,$stock_rows);
    }
}

JWRender::Display('show',array(
        'rows'  =>  $rows,
        'count_post' =>  $count_post,
        'follower_num' => $follower_num,
));
?>
