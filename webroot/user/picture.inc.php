<?php
function user_picture($idUser, $picSize)
{

	if ( empty($picSize) )
		$picSize = 'thumb48';

	$user_db_row = JWUser::GetUserInfo($idUser);

	JWPicture::Show($user_db_row['idPicture'], $picSize);
}
?>
