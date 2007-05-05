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

			if ( ! file_exists($filename) )
			{
				header ( "Location: " . JWTemplate::GetConst('UrlStrangerPicture') );
				exit(0);
			}

			$picType = 'gif';
			if ( !preg_match('/\.gif$/i',$filename) )
				$picType = 'jpg';

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
