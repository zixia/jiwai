<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();
$status_id = $_POST['id'];
$choice = $_POST['choice'];

$lingo = "TP $status_id $choice";
$type = 'web';
$robot_msg = new JWRobotMsg();
$robot_msg->Set($current_user_id, $type, $lingo);
$robot_msg->SetHeader( 'serverAddress', 'web@jiwai.de' );

$reply_msg = JWRobotLogic::ProcessMo( $robot_msg );

if( $reply_msg === false ) {
	JWLog::Instance()->Log(LOG_ERR, "TP($current_user_id, $status) failed");
}

if( false == empty( $reply_msg ) ){
	JWSession::SetInfo('notice', $reply_msg->GetBody() );
}

JWTemplate::RedirectBackToLastUrl('/');
?>
