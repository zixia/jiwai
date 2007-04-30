<?php
require_once('../../../jiwai.inc.php');
JWDebug::init();

JWUser::MustLogined();

if ( array_key_exists('status', $_REQUEST) ){
	if ( $status = $_REQUEST['status'] ){

		$id = JWUser::GetCurrentUserInfo('id');

		//JWDebug::trace( "update $id $status" );
		if ( ctype_digit($id) ){
			JWStatus::Update($id, $status);
		}else{
			JWDebug::trace( "status/update can't get user id" );
		}
	}
}

header ("Location: /wo/");
?>
