<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de File Class
 */
class JWFile {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFile
	 */
	static $msInstance;

	/**
	 * path_config
	 *
	 * @var
	 */
	static $msUserHome;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWFile
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
		$config 	= JWConfig::instance();
		$directory 	= $config->directory;

		self::$msUserHome	= $directory->user->home ;

		if ( ! is_writeable(self::$msUserHome) )
		{
			throw new JWException("can't write dir");
		}
	}

	/*
	 *
	 * @param	string	picture type (jpg | gif)
	 * @param	string	picture size (picture | thumb48 | thumb24)
	 * @param	int		idUser, null if current user.

	 * @return string	filename with full path
	 */
	static public function GetUserPicture($picType, $picSize='thumb48', $idUser=null)
	{
		self::Instance();


		if ( null===$idUser )
			$idUser = JWUser::GetCurrentUserId();

		if ( null===$idUser )
			throw new JWException("no session found!");

		$user_home 	= self::$msUserHome . $idUser . '/profile_image/';

		switch ($picSize)
		{
			case 'picture':
				return $user_home . 'picture.' . $picType;
			case 'thumb48':
				return $user_home . 'thumb48.' . $picType;
			case 'thumb24':
				return $user_home . 'thumb24.' . $picType;
			default:
				throw new JWException("unknown size: $picSize");
		}
	
		throw new JWException("unreachable");
	}


	/*
	 *
	 * @return bool
	 * @param	string	user named image file
	 */
	static public function SaveUserPicture($filePathName, $idUser=null)
	{
		self::Instance();


		if ( null===$idUser )
			$idUser = JWUser::GetCurrentUserId();


		$user_home 	= self::$msUserHome . $idUser . '/profile_image/';

		if ( ! file_exists($user_home) )
			mkdir($user_home,0700,true);

		if ( ! is_writeable($user_home) )
			throw new JWException("$user_home unwriteable");


		$file_type = 'jpg';
		if ( preg_match('/\.gif$/i',$filePathName) )
			$file_type = 'gif';

		$user_picture_path_name	= $user_home . "picture." . $file_type;
		$user_thumb48_path_name = $user_home . "thumb48." . $file_type;
		$user_thumb24_path_name = $user_home . "thumb24." . $file_type;


		if ( ! self::ConvertPictureBig($filePathName,$user_picture_path_name) )
			return false;

		if ( ! self::ConvertThumbnail48($user_picture_path_name,$user_thumb48_path_name) )
			return false;

		if ( ! self::ConvertThumbnail24($user_thumb48_path_name,$user_thumb24_path_name) )
			return false;

		return true;
	}

	static public function ConvertPictureBig($srcFile, $dstFile)
	{
		$srcFile = escapeshellarg($srcFile);
		$dstFile = escapeshellarg($dstFile);

		$cmd = <<<_CMD_
convert $srcFile \\
  -auto-orient \\
  -thumbnail '800x>' \\
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
}
?>
