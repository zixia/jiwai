<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  wqsemc@jiwai.com
 * @version	 $Id$
 */

/**
 * JWPlugins
 */

class JWPlugins 
{
	/**
	 * Instance of this singleton
	 *
	 * @var 
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return 
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}

	static public function GetInfo( $status_row )
	{
		$status = $status_row['status'];
		if ( preg_match(	'#'
					// head_str
					. '^(.*?)'
					. 'http://'
					// url_domain
					. '([' . '\x00-\x1F' ./*' '*/ '\x21-\x2B' ./*','*/ '\x2D-\x2E' ./*'/'*/ '\x30-\x39' ./*':'*/ '\x3B-\x7F' . ']+)'
					// url_path
					. '([' . '\x00-\x09' ./*\x0a(\n)*/ '\x0B-\x0C' ./*\x0d(\r)*/ '\x0E-\x1F' ./*' '*/ '\x21-\x7F' . ']*)'
					// tail_str
					. '(.*)$#is'
					, $status
					, $matches 
			       ) )
		{
			//die(var_dump($matches));
			$head_str = htmlspecialchars($matches[1]);
			$url_domain = htmlspecialchars($matches[2]);
			$url_path = htmlspecialchars($matches[3]);
			$tail_str = htmlspecialchars($matches[4]);

			/*
			 *	检查 url path 是否为真正的 url path
			 */
			if (!empty($url_path) && preg_match('#[^/:]#', $url_path[0]) )
			{
				$tail_str = $url_path . $tail_str;
				$url_path = '';
			}
			$url = 'http://' .$url_domain . $url_path;

			$photo_info = JWPlugins_Yupoo::GetPhotoInfo( $url );
			if( false == empty( $photo_info ) )
			{
				return array( 
						'src' => JWPlugins_Yupoo::BuildPhotoUrl( $photo_info ),
						'href' => $url,
					    );
			}
		}
		else if( 'Y'==$status_row['isMms'] ) 
		{
			$photo_row = JWPicture::GetDbRowById( $status_row['idPicture'] );
			$photo_subject = $photo_row['fileName'];
			$user_row = JWUser::GetDbRowById( $status_row['idUser'] );
			return array(
				'src' => JWPicture::GetUrlById($status_row['idPicture'], 'middle'),
				'href' => JW_SRVNAME .'/'. $user_row['nameUrl'] .'/mms/'. $status_row['id'],
			);
		}
		return null;
	}
}
