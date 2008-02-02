<?php 
$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];

$arr_count_param = JWSns::GetUserState($current_user_id);
$device_row = JWDevice::GetDeviceRowByUserId($current_user_id);
$active_options = array();

$supported_device_types = JWDevice::GetSupportedDeviceTypes();
foreach ( $supported_device_types as $type )
{
	if ( isset($device_row[$type]) && $device_row[$type]['verified']  )
	{	
		$active_options[$type]	= true;
	}
	else
	{
		$active_options[$type] 	= false;
	}
}


$arr_friend_list = JWFollower::GetFollowingIds($current_user_id);

$via_device = JWUser::GetSendViaDevice($current_user_id);

$friend_request_num = JWFollowerRequest::GetInRequestNum($current_user_id);

$idUserVistors = JWSns::GetIdUserVistors( $current_user_id );

$arr_menu = array(
	array ('status', array($current_user_info)) , 
	array ('friend_req', array($friend_request_num)) , 
	array ('count', array($arr_count_param)) , 
	array ('jwvia', array($active_options, $via_device)) ,
	array ('invite'	, array()) ,
	array ('bookmarklet', array()),
	array ('separator', array()) ,
	array ('vistors', array($idUserVistors)) , 
	array ('separator', array()) ,
	array ('friend', array($arr_friend_list)) , 
	array ('listfollowing', array('wo', count($arr_friend_list)>60 )) ,
);
	
JWTemplate::sidebar( $arr_menu );
?>
