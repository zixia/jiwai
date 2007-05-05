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
		$config 	= JWConfig::instance();
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
}
?>
