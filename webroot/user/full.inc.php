<?php
function user_picture($idUser, $picSize)
{

	if ( empty($picSize) )
		$picSize = 'thumb48';

	$photo_info = JWUser::GetUserInfoById($idUser, 'photoInfo');

	if ( empty($photo_info) )
	{
		header ( "Location: http://asset.jiwai.de/img/stranger.gif" );
		exit(0);
	}


	$arr_picture_info = JWUser::GetPictureInfo($photo_info);
	
	$picType = $arr_picture_info['type'];


	if ( 'gif'!=$picType )
		$picType = 'jpg';


	switch ($picSize)
	{
		case 'picture': // let JWFile choose 
		case 'thumb48':// let JWFile choose 
		case 'thumb24':
			$filename = JWFile::GetUserPicture($picType,$picSize, $idUser);

			header("Content-Type: image/$picType");
			header("Content-Length: " . filesize($filename));

			$fp = fopen($filename, 'rb');
			fpassthru($fp);

			exit(0);
		default:
			throw new JWException("unsupport size $picSize");
	}

	exit(0);
}
?>
