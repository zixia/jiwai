<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_info = JWUser::GetCurrentUserInfo();

$redirect_url = '/'.urlEncode($current_user_info['nameUrl']).'/mms/';

JWTemplate::RedirectToUrl( $redirect_url );
?>
