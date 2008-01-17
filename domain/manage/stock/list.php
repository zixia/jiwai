<?php
require_once('./config.inc.php');

if( $_POST )
{
    $stock_num = isset( $_POST['stock_num'] ) ? $_POST['stock_num'] : NULL;
    if( false == preg_match( '/^(\d{3}|\d{6})$/', $stock_num ) )
    {
        SetNotice("股票帐户只能是：3位数字的分类号、6位数字的股票代码。", true);
    }
    $is_stock = strlen( $stock_num ) == 6;
    $tag_id = JWTag::GetIdByDescription( $stock_num );

    if( false == $tag_id )
    {
        SetNotice("系统中还未登记 $stock_num 。", true);
    }

    $tag_ids = JWFollowRecursion::GetSuperior( $tag_id, 1, $is_stock );

    if( empty( $tag_ids[1] ) )
    {
        SetNotice("此帐户 $stock_num 不存在级联关系。", true);
    }
    $tag_rows = JWTag::GetDbRowById($tag_ids[1]);
    if( $is_stock )
    {
        $rows = $stock_num.'的上级为：'.$tag_rows['description'].'&nbsp&nbsp账户名称：'.$tag_rows['name'];
    }
    else
    {
        $rows = $stock_num.'的下级为：'.$tag_rows['description'].'&nbsp&nbsp账户名称：'.$tag_rows['name'];
    }

}
JWRender::display("list",array(
            'rows' => $rows,
            ));

?>
