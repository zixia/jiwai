<?php
require_once( './config.inc.php' );

if( $_POST )
{
    $stock_num = isset($_POST['stockNum']) ? $_POST['stockNum'] : null;
    $full_name = isset($_POST['nameFull']) ? $_POST['nameFull'] : null;
    $is_stock = strlen( $stock_num ) == 6;
    $admin = $_SESSION['idUser'];
    $tag_id = JWTag::GetIdByDescription( $stock_num );

    if( false == preg_match( '/^(\d{3}|\d{6})$/', $stock_num ) )
    {
        SetNotice("股票帐户只能是：3位数字的分类号、6位数字的股票代码。", true);
    }
    if( false == empty( $tag_id ) )
    {
        SetNotice( "该股票已被登记。",true);
    }
    else 
    {

        if( $tag_id = JWCommunity_User::CreateUserStock( $stock_num, $full_name ,null, $admin ))
        {
            $type = $is_stock ? JWComStockAccount::T_STOCK : JWComStockAccount::T_CATE;
            JWDB::SaveTableRow( 'ComStockAccount', array(
                        'idUser' => $tag_id,
                        'type' => $type,
                        ));
            $options = array(
                'filter' => 'N',
                'notify' => 'N',
            );
            
            $conference_id = JWConference::Create( $tag_id, $options ); 

            JWTag::SetConference( $tag_id , $conference_id );

            SetNotice('创立股票账户：'.$full_name.' 成功。', true);
          
        }
	}
}

JWRender::Display( 'createStockAccount' , array(
				'menu_nav' => 'createStockAccount',
			));
?>
