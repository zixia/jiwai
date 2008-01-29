<?php
function user_friends($idUser)
{
	$g_user_friends = true;
	$g_page_user_id = $idUser;

	$logined_user_info = JWUser::GetCurrentUserInfo(); 

	if ( $logined_user_info && $logined_user_info['id']==$g_page_user_id )
	{
		include(dirname(__FILE__).'/../wo/followers/index.php');
	}
	else
	{
		JWTemplate::RedirectTo404NotFound();
	}
}  // end function
?>
