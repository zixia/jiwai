<?php
require_once('./config.inc.php');

if( $_POST )
{
    $stock_num = isset( $_POST['stock_num'] ) ? $_POST['stock_num'] : NULL;
    
    if( false == preg_match( '/^(\d{3}|\d{6})$/', $stock_num ) )
    {
        SetNotice("股票帐户只能是：3位数字的分类号、6位数字的股票代码。", true);
    }
    
    $status = isset( $_POST['jw_status'] ) ? trim($_POST['jw_status']) : NULL;
    $tag_id = JWTag::GetIdByDescription( $stock_num );
    $user_info = JWUser::GetDbRowByNameScreen( $_SESSION['idUser'] );
      
    if( empty( $tag_id ) )
    {
        SetNotice("系统中还未登记此股票帐户.",true);
    }
    elseif( empty( $status ) )
    {
        SetNotice("总得说点什么吧？",true);
    }
    else 
    {
        $options_info = array(
            'idTag' => $tag_id,
        );
        $status_id = JWSns::UpdateStatus( $user_info['id'],$status,'web',NULL,'web@jiwai.de',$options_info );
        if( $status_id )
        {
            SetNotice( "发布股市信息成功,更新的编号：$status_id，通知到：$tag_id 。" , true);
        }
    }


}
    JWRender::display("info", array());
?>
