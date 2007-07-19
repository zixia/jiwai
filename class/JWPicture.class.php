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

	static $msPicturePath = "picture/";

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


	static public function Destroy($idPicture)
	{
		//我们要为每一条更新保留头像，所以不能轻易删除数据库中的图片

		$idPictures = JWDB_Cache::CheckInt($idPictures);


/*
 *	删除文件？定期做垃圾回收吧
 *
		$picture_path	= JWPicture::GetPathRel($idPicture);
		$abs_path		= JWFile::GetStorageAbsRoot() . $picture_path;

		if ( ! unlink ( $file_full_path_name ) )
		{
			JWLog::LogFuncName(LOG_CRIT, "unlink($file_full_path_name) failed");;
		}
 */

		return JWDB_Cache::DelTableRow('Picture', array('id'=>$idPicture));
	}


	static private function GetPathRel($idPicture)
	{
		$idPicture = JWDB_Cache::CheckInt($idPicture);

		self::Instance();

		/*
		 *	根据 id 将目录进行2层存储
		 */
		$d1 = intval($idPicture/(1000*1000));
		$d2 = intval( ($idPicture%(1000*1000)) / (1000) );
		$d3 = $idPicture % 1000;

		$hash_path = "$d1/$d2/$d3/";
		return self::$msPicturePath . $hash_path;
	}


	static private function GetFullPathNameRowByIds($idPictures, $picSize='thumb48')
	{
		self::Instance();

		// 7/19/07 zixia: ugly, 不应该在 JWPicture 里面调用 JWDB_Cache_Picture.
		//$db_rows = self::GetDbRowsByIds($idPictures);

		$db_rows = JWDB_Cache_Picture::GetDbRowsByIds($idPictures);

		foreach ( $idPictures as $picture_id )
		{
			$picture_row = $db_rows[$picture_id];
			$user_id 	=  $picture_row['idUser'];


			/*
			 *	构造目录结构
			 */
			$abs_path			= 	 JWFile::GetStorageAbsRoot() 
									.JWPicture::GetPathRel($picture_id);

			$abs_pathname		= $abs_path . $picSize . '.' . $picture_row['fileExt'];


			$path_map[$picture_id] = $abs_pathname;
		}

		return $path_map;
	}
	

	/*
	 *
	 * @param	string	picture type (jpg | gif)
	 * @param	string	picture size (picture | thumb48 | thumb24)
	 * @param	int		idUser, null if current user.

	 * @return string	filename with full path
	 */
	static public function GetFullPathNameById($idPicture, $picSize='thumb48')
	{
		$rows = JWPicture::GetFullPathNameRowByIds(array($idPicture),$picSize);
		return $rows[$idPicture];
	}


	/*
	 *	根据 idPicture 获取 DbRow 的详细信息
	 *	@param	array	idPicture
	 * 	@return	array	以 idPicture 为 key 的 picture db row
	 * 
	 */
	static public function GetDbRowsByIds($idPictures)
	{
		$return_db_rows = array();

		if ( empty($idPictures) )
			return $return_db_rows;

		if ( !is_array($idPictures) )
			throw new JWException('must array');

		$condition_in = JWDB_Cache::GetInConditionFromArray($idPictures);

		$sql = <<<_SQL_
SELECT
		*
		, id as idPicture
FROM	Picture
WHERE	id IN ($condition_in)
_SQL_;

		$db_rows = JWDB_Cache::GetQueryResult($sql,true);


		if ( !empty($db_rows) ){
			foreach ( $db_rows as $db_row ) {
				$return_db_rows[$db_row['id']] = $db_row;
			}
		}

		return $return_db_rows;
	}

	static public function GetDbRowById($idPicture)
	{
		$db_rows = self::GetDbRowsByIds(array($idPicture));
		return $db_rows[$idPicture];
	}

	static public function GetUrlRowByIds($idPictures, $picSize='thumb48')
	{
		$url_row = array();

		if ( empty($idPictures) )
			return $url_row;

		$picture_rows 	= self::GetDbRowsByIds($idPictures);
		
		foreach ( $idPictures as $picture_id )
		{
			if ( empty($picture_rows[$picture_id]) )
			{
				$url_row[$picture_id] = JWTemplate::GetConst('UrlStrangerPicture');
			}
			else
			{
				$asset_url_path = "/system/user/profile_image/"
								. $picture_rows[$picture_id]['idUser']
								. '/' . $picture_id
								. '/' . $picSize
								. '/' . $picture_rows[$picture_id]['fileName']
								. '.'
								. $picture_rows[$picture_id]['fileExt']
								;
				$url_row[$picture_id] = JWTemplate::GetAssetUrl($asset_url_path, false);
			}
		}

		return $url_row;
	}

	static public function GetUrlById($idPicture, $picSize='thumb48')
	{
		$url_row = JWPicture::GetUrlRowByIds(array($idPicture),$picSize);
		return $url_row[$idPicture];
	}


	/*
	 *
	 * 过期函数，过期函数，过期函数，未来删除
	 * 应使用 GetUrlById(idPicture)替换
	 *
	 * @param 	int		idUser
	 * @param 	enum	pictSize = ['thumb48' | 'thumb24' | 'picture']
	 * @return 	string	url of picture
	 */
	static public function GetUserIconUrl($idUser, $picSize='thumb48')
	{
		$user_db_row	= JWUser::GetUserInfo($idUser);

		$picture_id		= $user_db_row['idPicture'];

		return JWPicture::GetUrlById($picture_id,$picSize);
	}


	/*
	 *	通过 idUser 和 上载的文件de MD5，检查数据库中，用户是否在以前上传过这个文件（上传过则系统有副本，不用再保存）
	 *
	 *	如果存在，返回 Picture 表的 id 主键，供使用。
	 */	
	static public function GetIdByMd5($idUser, $md5, $class='ICON')
	{
		$idUser	= JWDB_Cache::CheckInt($idUser);
		
		if ( empty($md5) )
			return 0;

		return JWDB_Cache::ExistTableRow('Picture', array(	 'idUser'	=> $idUser
														,'md5'		=> $md5
													)
									);
	}


	/*
	 *
	 * @param	string	absFilePathName	user named image file

	 * @return 	int		false if fail, otherwise return new PK of picture TB
	 */
	static public function SaveUserIcon($idUser, $absFilePathName)
	{
		if ( ! preg_match('#(?P<file_name>[^/]+)\.(?P<file_ext>[^.]+)$#', $absFilePathName, $matches) )
		{
			unlink ( $absFilePathName );
			return ;
		}


		/*
		 *	检查用户是否以前上传过这个图片
		 */
		$md5  = md5_file($absFilePathName);

		$picture_id = JWPicture::GetIdByMd5($idUser, $md5, 'ICON');

		if ( $picture_id )
			return $picture_id;


		$file_name 	= $matches['file_name'];
		$file_ext 	= $matches['file_ext'];

		$dst_file_type = 'jpg';
		if ( 'gif'==$file_ext )
			$dst_file_type = 'gif';


		self::Instance();

		$picture_id = JWDB_Cache::SaveTableRow('Picture', array(	 'idUser'	=> $idUser
															,'class'	=> 'ICON'
															,'fileName'	=> $file_name
															,'fileExt'	=> $dst_file_type
															,'md5'		=> $md5
															,'timeCreate'	=> JWDB_Cache::MysqlFuncion_Now()
														)
										);
		if ( empty($picture_id) )
			return 0;

		$abs_storage_root	= JWFile::GetStorageAbsRoot();
		$picture_path		= self::GetPathRel($picture_id);
		$abs_path			= $abs_storage_root . $picture_path;

		if ( ! file_exists($abs_path) )
			mkdir($abs_path,0770,true);

		if ( ! is_writeable($abs_path) )
			throw new JWException("$user_path unwriteable");

		$rel_picture_path_name = $picture_path . "picture." . $dst_file_type;
		$rel_thumb48_path_name = $picture_path . "thumb48." . $dst_file_type;
		$rel_thumb24_path_name = $picture_path . "thumb24." . $dst_file_type;


		$ret = true;

		if ( $ret && ! self::ConvertPictureBig( 	 $absFilePathName
											,($abs_storage_root . $rel_picture_path_name)) ){
			$ret = false;
		}


		if ( $ret && ! self::ConvertThumbnail48(	 ($abs_storage_root . $rel_picture_path_name)
											,($abs_storage_root . $rel_thumb48_path_name)) ){
			$ret = false;
		}

		if ( $ret && ! self::ConvertThumbnail24(	 ($abs_storage_root . $rel_thumb48_path_name)
											,($abs_storage_root . $rel_thumb24_path_name)) ){
			$ret = false;
		}

		
		if ( $ret && ! JWFile::Save( array($rel_picture_path_name,$rel_thumb48_path_name,$rel_thumb24_path_name) ) )
			$ret = false;

		unlink ( $absFilePathName );

		if ( ! $ret )
		{
			JWDB_Cache::DelTableRow('Picture', array('id'=>$picture_id) );
			$picture_id = false;
		}

		return $picture_id;
	}

	static public function ConvertPictureBig($srcFile, $dstFile)
	{
		$srcFile = escapeshellarg($srcFile);
		$dstFile = escapeshellarg($dstFile);

		/*
		 *	-coalesce 方式gif缩小的时候出现问题。http://wiki.flux-cms.org/display/BLOG/Resizing+animated+GIFs+with+ImageMagick
		 */
		$cmd = <<<_CMD_
convert $srcFile \\
  -coalesce \\
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

	static public function Show($idPicture, $picSize='thumb48')
	{
		switch ($picSize)
		{
			default:	// fall to thumb48
				$picSize = 'thumb48';

			case 'origin': // let JWFile choose 
			case 'picture': // let JWFile choose 
			case 'thumb48':// let JWFile choose 
			case 'thumb24':
				$filename = self::GetFullPathNameById($idPicture, $picSize);

				$picType = 'gif';
				if ( !preg_match('/\.gif$/i',$filename) )
					$picType = 'jpg';

				if ( !file_exists($filename) )
				{
					header ( "Location: " . JWTemplate::GetConst('UrlStrangerPicture') );
					exit(0);
				}

				header('Content-Type: image/'.$picType);
				header('Content-Length: '.filesize($filename));
				header('Last-Modified: '.date(DATE_RFC822, filemtime($filename)));
				header('Expires: '.date(DATE_RFC822, time()+3600*24*365*10));
				header('Pragma: public');
				//header('X-Sendfile: '.$filename);
				//header("cache-control: max-age=94608000");

				$fp = fopen($filename, 'rb');
				fpassthru($fp);

				break;

		}

		exit(0);
	}

	/*
	 *
	 * @param	string	absFilePathName	user named image file

	 * @return 	int		false if fail, otherwise return new PK of picture TB
	 */
	static public function SaveBg($idUser, $absFilePathName)
	{
		if ( ! preg_match('#(?P<file_name>[^/]+)\.(?P<file_ext>[^.]+)$#', $absFilePathName, $matches) )
		{
			unlink ( $absFilePathName );
			return ;
		}


		/*
		 *	检查用户是否以前上传过这个图片
		 */
		$md5  = md5_file($absFilePathName);

		$picture_id = JWPicture::GetIdByMd5($idUser, $md5, 'BG');

		if ( $picture_id )
			return $picture_id;


		$file_name 	= $matches['file_name'];
		$file_ext 	= $matches['file_ext'];

		$dst_file_type = 'jpg';
		if ( 'gif'==$file_ext )
			$dst_file_type = 'gif';


		self::Instance();

		$picture_id = JWDB_Cache::SaveTableRow('Picture', array(	 'idUser'	=> $idUser
															,'class'	=> 'BG'
															,'fileName'	=> $file_name
															,'fileExt'	=> $dst_file_type
															,'md5'		=> $md5
															,'timeCreate'	=> JWDB_Cache::MysqlFuncion_Now()
														)
										);
		if ( empty($picture_id) )
			return 0;

		$abs_storage_root	= JWFile::GetStorageAbsRoot();
		$picture_path		= self::GetPathRel($picture_id);
		$abs_path			= $abs_storage_root . $picture_path;

		if ( ! file_exists($abs_path) )
			mkdir($abs_path,0770,true);

		if ( ! is_writeable($abs_path) )
			throw new JWException("$user_path unwriteable");
		
		$rel_bg_path_name = $picture_path . "origin." . $dst_file_type;

		$ret = true;

		if ( $ret && ! copy(	 $absFilePathName
								,$abs_storage_root . $rel_bg_path_name 
							)
			)
		{
			$ret = false;
		}


		if ( $ret && ! JWFile::Save( array($rel_bg_path_name) ) )
			$ret = false;

		unlink ( $absFilePathName );

		if ( ! $ret )
		{
			JWDB_Cache::DelTableRow('Picture', array('id'=>$picture_id) );
			$picture_id = false;
		}

		return $picture_id;
	}

}
?>
