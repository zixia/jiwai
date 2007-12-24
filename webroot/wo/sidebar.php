<?php 
$arr_count_param	= JWSns::GetUserState($logined_user_id);


$device_row			= JWDevice::GetDeviceRowByUserId($logined_user_id);

$active_options = array();

$supported_device_types = JWDevice::GetSupportedDeviceTypes();

foreach ( $supported_device_types as $type )
{
	if ( isset($device_row[$type]) 
				&& $device_row[$type]['verified']  )
	{	
		$active_options[$type]	= true;
	}
	else
	{
		$active_options[$type] 	= false;
	}
}


$arr_friend_list = JWFollower::GetFollowingIds($logined_user_id);

$via_device = JWUser::GetSendViaDevice($logined_user_id);

$friend_request_num = JWFollowerRequest::GetInRequestNum($logined_user_id);

$idUserVistors = JWSns::GetIdUserVistors( $logined_user_id );

$arr_menu = array(
	array ('status', array($logined_user_info)) , 
	array ('friend_req', array($friend_request_num)) , 
	array ('count', array($arr_count_param)) , 
	array ('jwvia', array($active_options, $via_device)) ,
	//array ('invite'	, array()) ,
	array ('bookmarklet', array()),
	array ('separator', array()) ,
	array ('vistors', array($idUserVistors)) , 
	array ('separator', array()) ,
	array ('friend', array($arr_friend_list)) , 
	array ('listfollowing', array('wo', count($arr_friend_list)>60 )) ,
);
	
JWTemplate::sidebar( $arr_menu );
?>
