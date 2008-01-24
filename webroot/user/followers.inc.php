<?php
function user_friends($idUser)
{
	$g_user_friends = true;
	$g_page_user_id = $idUser;

	$logined_user_info 	= JWUser::GetCurrentUserInfo();
	$zdmin_file = FRAGMENT_ROOT . 'zdminuser/zdmin';
	$zdmin_users = file_get_contents( $zdmin_file );
	$user_array = explode( ',', $zdmin_users );

	if ( !in_array( $logined_user_info['id'], $user_array ) )
		JWTemplate::RedirectTo404NotFound();

	include(dirname(__FILE__).'/../wo/followers/index.php');
}  // end function
?>
