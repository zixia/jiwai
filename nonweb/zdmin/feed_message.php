<?php
require_once('./function.php');

if( isset( $_POST ))
{
    $device = isset( $_POST['select'] ) ? $_POST['select'] : NULL;
    $time = isset( $_POST['select2'] ) ? $_POST['select2'] : NULL;
    $deal_status = isset( $_POST['select3'] ) ? $_POST['select3'] : NULL;
    switch( $deal_status )
    {
        case '未处理':
            $deal_status = 'NONE';
            break;
        case '已处理':
            $deal_status = 'FIXED';
            break;
        case '不予处理':
            $deal_status = 'WONTFIX';
            break;
        case '全部':
            $deal_status = NULL;
            break;
    }

    switch( $time )
    {
        case '不限':
            $time_begin = NULL;
            break;
        case '今天':
            $time_begin = date("Y-m-d");
            break;
        case '三天内':
            $time_begin = date("Y-m-d",time()-3600*24*3);
            break;
        case '一周内':
            $time_begin = date("Y-m-d",time()-3600*24*7);
            break;
        case '一月内':
            $time_begin = date("Y-m-d",time()-3600*24*30);
            break;
    }
    
    $time_end = date('Y-m-d', time()+86400);
    $result = JWFeedBack::GetDbRowByCondition($device,null,$deal_status,$time_begin,$time_end);

} 
if( isset($_GET['id']) )
{
    $id = $_GET['id'];
    $setDeal = JWFeedBack::SetDealStatus($id,'WONTFIX');
    JWTemplate::RedirectToUrl( '/feed_message.php' );
}
if( isset($_GET['deal']))
{
    $id = $_GET['deal'];
    $setDeal = JWFeedback::SetDealStatus($id,'FIXED');
    JWTemplate::RedirectToUrl( '/feed_message.php' );
}
if( isset($_GET['delete']))
{
    $id = $_GET['delete'];
    $delDeal = JWFeedBack::Destroy($id);
    JWTemplate::RedirectToUrl( '/feed_message.php' );
}
foreach( $result as $key => $value )
{
    $user_info1 = JWUser::GetUserInfo($value['idUser']);
    $time = $value['timeCreate'];
    $times = explode(" ",$time);
    $one[$key] = $value;
    $user_info[$key] = $user_info1;
}
$render = new JWHtmlRender();
$render->display("feed_message", array(
            'one' => $one,
            'user_info' => $user_info,
	    'menu_nav' => 'feed_message',
    ));
?>

