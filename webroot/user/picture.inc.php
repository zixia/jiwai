<?php
function user_picture($idUser, $picSize)
{

	if ( empty($picSize) )
		$picSize = 'thumb48';


	switch ($picSize)
	{
		case 'picture': // let JWFile choose 
		case 'thumb48':// let JWFile choose 
		case 'thumb24':
			$filename = JWPicture::GetUserIconFullPathName($idUser, $picSize);

			$picType = 'gif';
			if ( !preg_match('/\.gif$/i',$filename) )
				$picType = 'jpg';

			header("Content-Type: image/$picType");
			header("Content-Length: " . filesize($filename));

			$fp = @fopen($filename, 'rb');

			if ( false==$fp )
			{
				header ( "Location: " . JWTemplate::GetConst('UrlStrangerPicture') );
				exit(0);
			}

			fpassthru($fp);

			exit(0);
		default:
			throw new JWException("unsupport size $picSize");
	}

	exit(0);
}
?>
