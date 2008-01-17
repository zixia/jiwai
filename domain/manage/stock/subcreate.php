<?php
require_once('./config.inc.php');

if( $_POST )
{
    $superior_tag = isset( $_POST['superior_tag'] ) ? $_POST['superior_tag'] : NULL;
    $tag = isset( $_POST['tag'] ) ? $_POST['tag'] : NULL;
    if( false == preg_match( '/^(\d{3}|\d{6})$/', $superior_tag ) || false == preg_match( '/^(\d{3}|\d{6})$/', $tag ))
    {
        SetNotice("参数只能是：3位数字的分类号、6位数字的股票代码。", true);
    }
    $is_stock = strlen( $superior_tag ) == 6;
    $superior_tag_id = JWTag::GetIdByDescription( $superior_tag );
    $tag_id = JWTag::GetIdByDescription( $tag );

    if( empty( $superior_tag_id ) )
    {
        SetNotice("系统中未登记 $superior_tag 股票", true);
    }
    if( empty( $tag_id ) )
    {
        SetNotice("系统中未登记$tag 股票", true);
    }
    $superior_tag_info = JWTag::GetDbRowById( $superior_tag_id );
    
    $tag_info = JWTag::GetDbRowById( $tag_id );
    if( $is_stock )
    {
        JWFollowRecursion::Create( $superior_tag_info['id'], $tag_info['id'] );
        SetNotice("创建 $superior_tag 与 $tag 的级联成功");
    }
    else
    {
        JWFollowRecursion::Create( $tag_info['id'], $superior_tag_info['id'] );
        SetNotice("创建 $superior_tag 与 $tag 的级联成功");
    }

}

JWRender::display("subcreate",array());
?>
