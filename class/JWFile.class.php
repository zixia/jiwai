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
	static private $msInstance;

	/**
	 * path_config
	 *
	 * @var
	 */
	static private $msStorageAbsRoot;


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
		$config 	= JWConfig::Instance();
		$directory 	= $config->directory;

		self::$msStorageAbsRoot		= 	$directory->storage->root ;

		if ( ! is_writeable(self::$msStorageAbsRoot) ){
			throw new JWException("can't write dir");
		}
	}

	static public function GetStorageAbsRoot()
	{
		self::Instance();
		return self::$msStorageAbsRoot;
	}

	/*
	 *	Save file ( relative to the Storage Root ) to Storage System ( if we have more then one storage in the furture )
	 *	@param	string/array of strig	relativeFilePathName	相对路径的文件名
	 *	@return	bool					全部存储成功返回true，否则false
	 */
	static public function Save( $relativeFilePathNames )
	{
		if ( is_array($relativeFilePathNames) )
		{
			foreach ( $relativeFilePathNames as $rel_file_path_name )
			{
				if ( ! self::Save($rel_file_path_name) )
					return false;
			}
		}
		
		// TODO: save a file to remote(furture) storage system here.
		return true;
	}


	/**
	* Removes the directory and all its contents.
	* 
	* @param string the directory name to remove
	* @param boolean whether to just empty the given directory, without deleting the given directory.
	* @return boolean True/False whether the directory was deleted.
	*/
	function DeleteDirectory($dirname,$emptyOnly=false) 
	{
		// 太危险了，还是把这个函数封装起来吧。
		// 定期用程序检查作文件系统的垃圾回收吧。
		throw new JWException("封印！");

		if (!is_dir($dirname))
			return false;

		$dscan = array(realpath($dirname));
		$darr = array();
		while (!empty($dscan)) 
		{
			$dcur = array_pop($dscan);
			$darr[] = $dcur;
			if ($d=opendir($dcur)) 
			{
				while ($f=readdir($d)) 
				{
					if ($f=='.' || $f=='..')
						continue;
					$f=$dcur.'/'.$f;
					if (is_dir($f))
						$dscan[] = $f;
					else
						unlink($f);
				}
				closedir($d);
			}
		}

		$i_until = ($emptyOnly)? 1 : 0;
		for ($i=count($darr)-1; $i>=$i_until; $i--) 
		{
			echo "\nDeleting '".$darr[$i]."' ... ";
			if (rmdir($darr[$i]))
				echo "ok";
			else
				echo "FAIL";
		}
		return (($emptyOnly)? (count(scandir)<=2) : (!is_dir($dirname)));
	}

}
?>
