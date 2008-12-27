<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

//JWLogin::MustLogined(false);
//$current_user_id = JWLogin::GetCurrentUserId();
//$vipIds = array(1, 89, 863, 2802, 77297);
//
//if (!in_array($current_user_id, $vipIds)) {
//    JWApi::OutHeader(401, true);
//}
//
//$channel = join('/', array('chat', $current_user_id));
$channel = join('/', array('chat', 2802));
JWPubSub_Msgbus::Subscribe($channel);
?>
