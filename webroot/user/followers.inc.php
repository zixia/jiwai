<?php
function user_friends($idUser)
{
	$g_user_friends = true;
	$g_page_user_id = $idUser;

	include(dirname(__FILE__).'/../wo/followers/index.php');
}  // end function
?>
