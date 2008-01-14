<?php
require_once('./config.inc.php');

if( $_POST )
{
    $stock_num = isset( $_POST['stock_num'] ) ? $_POST['stock_num'] : NULL;
    if( false == preg_match( '/^(\d{3}|\d{6})$/', $stock_num ) )
    {
        SetNotice( "参数只能是：3位数字的分类号、6位数字的股票代码。", true);
    }
    $is_stock = strlen( $stock_num ) == 6;
    $tag_id = JWTag::GetIdByDescription( $stock_num );
    if( false == $tag_id )
    {
        SetNotice("系统中还未登记此 $stock_num 股票。", true);
    }
    $tag_ids = JWFollowRecursion::GetSuperior( $tag_id, 1, $is_stock );

    if( empty( $tag_ids ) )
    {
        SetNotice("此股票 $stock_num 不存在级联关系。", true);
    }


    $low_tag_id = $is_stock ? $tag_id : $tag_ids[1];
    $superior_tag_id = $is_stock ? $tag_ids[1] : $tag_id;

    $rows = JWFollowRecursion::Destroy( $low_tag_id, $superior_tag_id );
    if( $rows )
    {

        $tag_info = $is_stock ? JWTag::GetDbRowById( $superior_tag_id ) : JWTag::GetDbRowById( $low_tag_id );

        if( $is_stock )
        {
            SetNotice('删除'. $stock_num.' 与其上级'. $tag_info['description'].' 的级联成功。',true);
        }
        else
        {
            SetNotice('删除'. $stock_num.' 与其下级'. $tag_info['description'].' 的级联成功。',true);
        }

    }
    else
    {
        SetNotice("删除失败，请重试。",true);
    }
}

JWRender::display('subdelete', array());
?>
