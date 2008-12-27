<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_id		= JWLogin::GetCurrentUserId();

$ui = new JWDesign($user_id);
$ui->Destroy();

JWSession::SetInfo('notice', '恢复缺省配色方案成功');

if ( array_key_exists('HTTP_REFERER',$_SERVER) )
    $redirect_url = $_SERVER['HTTP_REFERER'];
else
    $redirect_url = '/wo/account/profile_settings';

header('Location: ' . $redirect_url);
exit(0);
?>
