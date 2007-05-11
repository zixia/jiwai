<?php
function user_favourites($idUser)
{
	$g_user_favourites = true;
	$g_page_user_id = $idUser;

	include(dirname(__FILE__).'/../wo/favourites/index.php');
}  // end function
?>
