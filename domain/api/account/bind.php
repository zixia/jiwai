<?php
require_once("../../../jiwai.inc.php");

extract($_REQUEST, EXTR_IF_EXISTS);

$user_id = JWApi::GetAuthedUserId();
if( ! $user_id ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$r = JWVender::Bind($user_id, $_REQUEST['vender_id'], $_REQUEST['vender_user'], 
	array('display_name'=>$_REQUEST['vender_user_display_name'], 'profile_url'=>$_REQUEST['vender_user_profile_url']));

?>
