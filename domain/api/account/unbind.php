<?php
require_once("../../../jiwai.inc.php");

$user_id = JWApi::GetAuthedUserId();
if( ! $user_id ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

JWVender::Unbind($user_id, $_REQUEST['vender_id']);

?>
