<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Picture Class
 */
class JWPicture {
	/**
	 * Instance of this singleton
	 *
	 * @var JWPicture
	 */
	static $msInstance;

	static $msUserPath = "user/";

	/**
	 * Instance of this singleton class
	 *
	 * @return JWPicture
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}


	/*
	 * supprot 'ICON' class only.
	 */
	static public function GetPictureByUserId($idUser)
	{
		$idUser = intval($idUser);
		
		if ( 0>=$idUser )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT	*
FROM	Picture
WHERE	idUser=$idUser
		AND class='ICON'
_SQL_;

		return JWDB::GetQueryResult($sql);
	}


	/*
	 *
	 * @param	string	picture type (jpg | gif)
	 * @param	string	picture size (picture | thumb48 | thumb24)
	 * @param	int		idUser, null if current user.

	 * @return string	filename with full path
	 */
	static public function GetUserIconFullPathName($idUser, $picSize='thumb48')
	{
		self::Instance();

		$abs_storage_root	= JWFile::GetStorageAbsRoot();

		$picture_info		= JWPicture::GetPictureByUserId($idUser);

		$abs_pathname		= $abs_storage_root
								. self::$msUserPath . $idUser . '/profile_image/'
								. $picSize . "." . $picture_info['fileExt'];

		return $abs_pathname;
	}


	/*
	 *
	 * @return 	int		0 if fail, otherwise return new PK of picture TB
	 * @param	string	absFilePathName	user named image file
	 */
	static public function SaveUserIcon($idUser, $absFilePathName)
	{
		if ( ! preg_match('#(P<file_name>[^/]+)\.(P<file_ext)[^.]+)$/', $absFilePathName, $matches) )
			return ;

		$file_name 	= $matches['file_name'];
		$file_ext 	= $matches['file_ext'];


		self::Instance();

		$abs_storage_root	= JWFile::GetStorageAbsRoot();

		$rel_path			= self::$msUserPath 
									. $idUser . '/profile_image/';

		$abs_path			= $storage_abs_root . $rel_path;

		if ( ! file_exists($abs_path) )
			wkdir($abs_path,0770,true);

		if ( ! is_writeable($abs_path) )
			throw new JWException("$user_path unwriteable");

		$dst_file_type = 'jpg';
		if ( 'gif'==$file_ext )
			$dst_file_type = 'gif';

		$rel_picture_path_name = $rel_path . "picture." . $dst_file_type;
		$rel_thumb48_path_name = $rel_path . "thumb48." . $dst_file_type;
		$rel_thumb24_path_name = $rel_path . "thumb24." . $dst_file_type;


		if ( ! self::ConvertPictureBig( 	 $absFilePathName
											,($abs_storage_root . $rel_picture_path_name)) ){
			return false;
		}

		if ( ! self::ConvertThumbnail48(	 ($abs_storage_root . $rel_picture_path_name)
											,($abs_storage_root . $rel_thumb48_path_name)) ){
			return false;
		}

		if ( ! self::ConvertThumbnail24(	 ($abs_storage_root . $rel_thumb48_path_name)
											,($abs_storage_root . $rel_thumb24_path_name)) ){
			return false;
		}

		
		if ( ! JWFile::Save( array($rel_picture_path_name,$rel_thumb48_path_name,$rel_thumb24_path_name) ) )
			return false;

		$sql = <<<_SQL_
REPLACE	Picture
SET		 idUser=$idUser
		,class='ICON'
		,fileName='$file_name'
		,fileExt='$dst_file_type'
		,timeCreate=NOW()
_SQL_;

		$result = JWDB::Execute($sql);

		return JWDB::GetInsertId();
	}

	static public function ConvertPictureBig($srcFile, $dstFile)
	{
		$srcFile = escapeshellarg($srcFile);
		$dstFile = escapeshellarg($dstFile);

		$cmd = <<<_CMD_
convert $srcFile \\
  -auto-orient \\
  -thumbnail '500x>' \\
  $dstFile
_CMD_;
		if ( false===system($cmd,$ret) )
			return false;

		return 0===$ret;
	}


	static public function ConvertThumbnail48($srcFile, $dstFile)
	{
		$srcFile = escapeshellarg($srcFile);
		$dstFile = escapeshellarg($dstFile);

		$cmd = <<<_CMD_
convert $srcFile	 \\
  -resize x96		 \\
  -resize '96x<'	 \\
  -resize 50%		 \\
  -gravity center	 \\
  -crop 48x48+0+0	 \\
  +repage			 \\
  $dstFile
_CMD_;

		if ( false===system($cmd,$ret) )
			return false;

		return 0===$ret;
	}


	static public function ConvertThumbnail24($srcFile, $dstFile)
	{
		$srcFile = escapeshellarg($srcFile);
		$dstFile = escapeshellarg($dstFile);

		$cmd = <<<_CMD_
convert $srcFile	 \\
  -resize x48		 \\
  -resize '48x<'	 \\
  -resize 50%		 \\
  -gravity center	 \\
  -crop 24x24+0+0	 \\
  +repage			 \\
  $dstFile
_CMD_;

		if ( false===system($cmd,$ret) )
			return false;

		return 0===$ret;
	}

	/*
	 * @param 	int		idUser
	 * @param 	enum	pictSize = ['thumb48' | 'thumb24' | 'picture']
	 * @return 	string	url of picture
	 */
	static public function GetUserIconUrl($idUser=null, $picSize='thumb48')
	{
		$picture_info	= self::GetPictureByUserId($idUser);

		// TODO return JWTemplate::GetAssetUrl() . 
		return "http://JiWai.de/$idUser/picture/$picSize/$picture_info[fileName].$picture_info[fileExt]?$picture_info[id]";
		//return JWTemplate::GetAssetUrl("/system/user/profile_image/$idUser");
	}

}
?>
