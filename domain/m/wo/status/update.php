<?php
require_once( '../../config.inc.php');
JWLogin::MustLogined();

$status = null;
extract( $_REQUEST, EXTR_IF_EXISTS);
$status = trim( $status );

if( $status ){ 
	$idUser = JWUser::GetCurrentUserInfo('id');
	/*
	 *	为了 /help/ 留言板的更新都自动加上 @help
	 */
	$help_user_id	= JWUser::GetUserInfo('help', 'idUser');
	if ( preg_match('#\.de/help/$#i', $_SERVER['HTTP_REFERER'])
			&& $idUser != $help_user_id
			&& !preg_match('/^@help /',$status) ) {
			$status = '@help ' . $status;
	}
	if ( !JWSns::UpdateStatus($idUser, $status) )
		JWLog::Instance()->Log(LOG_ERR, "Create($idUser, $status) failed");
}
JWTemplate::RedirectBackToLastUrl("Location: /wo/");
?>
