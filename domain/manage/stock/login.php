<?php
$user = $pass = null;
if ( $_POST ) {

	extract( $_POST, EXTR_IF_EXISTS );

	$user_id = JWUser::GetUserFromPassword( $user, $pass );

	if ( $user_id )
	{
		$zdmin_file = FRAGMENT_ROOT . 'zdminuser/stock';
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

require_once( dirname(__FILE__).'/config.inc.php' );
?>
