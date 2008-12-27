<?php
require_once( '../../../jiwai.inc.php' );
JWLogin::MustLogined(false);

$pathParam = trim( $_REQUEST['pathParam'], '/' );
$userInfo = JWUser::GetUserInfo( $pathParam );


if( false == empty($userInfo) ){
	$logined_user_id = JWLogin::GetCurrentUserId();
	JWSns::UnBlock( $logined_user_id, $userInfo['id'] );
	JWSession::SetInfo('notice', "解除阻止 $userInfo[nameScreen] 成功。");
}

JWTemplate::RedirectBackToLastUrl();
?>
