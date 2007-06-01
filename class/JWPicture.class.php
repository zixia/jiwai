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
	 *	根据 idUser 获取 Picture Row 的详细信息
	 *	@param	array	idUser
	 * 	@return	array	以 idUser 为 key 的 picture row
	 * 
	 */
	static public function GetPictureDbRowsByIds( $idUsers )
	{
		if ( empty($idUsers) )
			return array();

		if ( !is_array($idUsers) )
			throw new JWException('must array');

		$condition_in = JWDB::GetInConditionFromArray($idUsers);

		$sql = <<<_SQL_
SELECT	*, id as idPicture
FROM	Picture
WHERE	idUser IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) ) {
			$picture_map = array();
		} else {
			foreach ( $rows as $row ) {
				$picture_map[$row['idUser']] 	= $row;
			}
		}

		return $picture_map;
	}


	/*
	 *	过期函数
	 * supprot 'ICON' class only.
	 *	return	array	row of table Picture
	 */
	static public function GetPictureByUserId($idUser)
	{
		$idUser = intval($idUser);
		
		if ( 0>=$idUser )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT	*, id as idPicture
FROM	Picture
WHERE	idUser=$idUser
		AND class='ICON'
_SQL_;

		return JWDB::GetQueryResult($sql);
	}


	static private function GetUserIconFullPathNameRowsByIds($idUsers, $picSize='thumb48')
	{
		$abs_storage_root	= JWFile::GetStorageAbsRoot();

		$picture_rows		= JWPicture::GetPictureDbRowsByIds($idUsers);

		foreach ( $idUsers as $user_id )
		{
			$abs_pathname		= $abs_storage_root
									. self::$msUserPath . $user_id . '/profile_image/'
									. $picSize . "." . $picture_row[$user_id]['fileExt'];
			$path_map[$user_id] = $abs_pathname;
		}

		return $path_map;
	}
	

	static public function GetUserIconUrlRowsByIds($idUsers, $picSize='thumb48')
	{
		if ( empty($idUsers) )
			return array();

		$picture_rows 	= self::GetPictureDbRowsByIds($idUsers);
		
		foreach ( $idUsers as $user_id )
		{
			if ( isset($picture_rows[$user_id]) ) // 用户上传了头像
			{
				$url_rows[$user_id] = "http://JiWai.de/"
								. $picture_rows[$user_id]['idUser']
								. "/picture/$picSize/"
								. $picture_rows[$user_id]['fileName']
								. '.'
								. $picture_rows[$user_id]['fileExt']
								. "?"
								. $picture_rows[$user_id]['idPicture'];
			}
			else	// 用户没有头像
			{
				$url_rows[$user_id] = JWTemplate::GetConst('UrlStrangerPicture');
			}
		}
		return $url_rows;
	}

	/*
	 * 过期函数，未来删除
	 * @param 	int		idUser
	 * @param 	enum	pictSize = ['thumb48' | 'thumb24' | 'picture']
	 * @return 	string	url of picture
	 */
	static public function GetUserIconUrl($idUser, $picSize='thumb48')
	{
		$picture_info	= self::GetPictureByUserId($idUser);

		// TODO return JWTemplate::GetAssetUrl() . 
		return "http://JiWai.de/$idUser/picture/$picSize/$picture_info[fileName].$picture_info[fileExt]?$picture_info[id]";
		//return JWTemplate::GetAssetUrl("/system/user/profile_image/$idUser");
	}


	/*
	 *
	 * 过期函数，未来删除
	 * 	@desprited
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
		if ( ! preg_match('#(?P<file_name>[^/]+)\.(?P<file_ext>[^.]+)$#', $absFilePathName, $matches) )
		{
			unlink ( $absFilePathName );
			return ;
		}

		$file_name 	= $matches['file_name'];
		$file_ext 	= $matches['file_ext'];


		self::Instance();

		$abs_storage_root	= JWFile::GetStorageAbsRoot();

		$rel_path			= self::$msUserPath 
									. $idUser . '/profile_image/';

		$abs_path			= $abs_storage_root . $rel_path;

		if ( ! file_exists($abs_path) )
			mkdir($abs_path,0770,true);

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
			unlink ( $absFilePathName );
			return false;
		}

		unlink ( $absFilePathName );

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

		return JWDB::GetInsertedId();
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
}
?>
