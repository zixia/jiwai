<?php
require_once('../../../jiwai.inc.php');
JWDebug::init();

JWLogin::MustLogined();

if ( array_key_exists('status', $_REQUEST) ){
	if ( $status = $_REQUEST['status'] )
	{
		$idUser = JWUser::GetCurrentUserInfo('id');

		if ( !JWStatus::Create($idUser, $status) )
			JWLog::Instance()->Log(LOG_ERR, "Create($id, $status) failed");
	}
}

header ("Location: /wo/");
?>
