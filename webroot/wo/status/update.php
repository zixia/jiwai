<?php
require_once('../../../jiwai.inc.php');
JWDebug::init();

JWUser::MustLogined();

if ( array_key_exists('status', $_REQUEST) ){
	if ( $status = $_REQUEST['status'] )
	{
		$idUser = JWUser::GetCurrentUserInfo('id');

		if ( !JWStatus::Update($idUser, $status) )
			JWLog::Instance()->Log(LOG_ERR, "Update($id, $status) failed");
	}
}

header ("Location: /wo/");
?>
