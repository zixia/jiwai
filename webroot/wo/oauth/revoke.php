<?php
require_once('../../../jiwai.inc.php');

JWLogin::MustLogined();
$user_info = JWUser::GetCurrentUserInfo();

JWOAuth::RevokeToken($user_info['id'], $_GET['token']);
JWSession::SetInfo('notice', '授权已取消');
JWTemplate::RedirectToUrl('/wo/oauth/');
?>
