<?php
$in_login_page = true;
require_once( dirname(__FILE__) . '/function.php');

$username = $password = null;

if ( $_POST ) {

	extract( $_POST, EXTR_IF_EXISTS );

	$user_id = JWUser::GetUserFromPassword( $username, $password );

	if ( $user_id )
	{
		$zdmin_file = FRAGMENT_ROOT . 'zdminuser/zdmin';
		$zdmin_users = file_get_contents( $zdmin_file );
		$user_array = explode( ',', $zdmin_users );

		if ( in_array( $user_id, $user_array ) )
		{
			$_SESSION['idUser'] = $user_id;
			JWTemplate::RedirectToUrl( '/index.php' );
		}
	}	

	JWTemplate::RedirectToUrl( '/login.php' );
}

JWRender::display("login", array());
?>
