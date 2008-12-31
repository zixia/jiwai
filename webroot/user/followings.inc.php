<?php
function user_friends($idUser)
{
	$g_user_friends = true;
	$g_page_user_id = $idUser;

	$tab = array(
		'square' => array( '方格', 'javascript:;'),
	);
	$_GET['s'] = 1;
	include(dirname(__FILE__).'/../wo/followings/index.php');
}  // end function
?>
