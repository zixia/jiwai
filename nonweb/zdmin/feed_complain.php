<?php
require_once('./function.php');

if(isset($_POST))
{
    $time = isset($_POST['select2']) ? $_POST['select2'] : NULL;
    $deal_status = isset($_POST['select3']) ? $_POST['select3'] : NULL;

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
    { case '不限':
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

    if( $device=='不限')
        $device = NULL;
    $time_end = date('Y-m-d', time()+86400);
    $result = JWFeedBack::GetDbRowByCondition($device,JWFeedBack::T_COMPLAIN,$deal_status,$time_begin,$time_end);
}
if( isset($_GET['id']) )
{
    $id = $_GET['id'];
    $setDeal = JWFeedBack::SetDealStatus($id,'WONTFIX');
    JWTemplate::RedirectToUrl( '/feed_complain.php' );
}
if( isset($_GET['deal']))
{
    $id = $_GET['deal'];
    $setDeal = JWFeedBack::SetDealStatus($id,'FIXED');
    JWTemplate::RedirectToUrl( '/feed_complain.php' );
}
if( isset($_GET['delete']))
{
    $id = $_GET['delete'];
    $delDeal = JWFeedBack::Destroy($id);
    JWTemplate::RedirectToUrl( '/feed_complain.php' );
}
foreach( $result as $key => $value )
{   
    $user_info_report = JWUser::GetUserInfo($value['idUser']);
    $user_info_reported = JWUser::GetuserInfo($value['metaInfo']['user'],null,'nameUrl');
    $time = $value['timeCreate'];
    $times = explode(" ",$time);
    $one[$key] = $value;
    $user_info[$key] = $user_info_report;
    $reported_user_info[$key] = $user_info_reported;

}           

JWRender::Display("feed_complain", array(
            'one' => $one,
            'user_info' => $user_info,
            'user_info2' => $reported_user_info,
	    'menu_nav' => 'feed_complain',
            ));

?>

