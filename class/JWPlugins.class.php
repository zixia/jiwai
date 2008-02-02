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

	static private $plugin_names = array(
		'Yupoo',
		'Box',
		'Yobo',
		'Video',
		'Flickr',
	);

	static public function GetPluginResult( $status_row )
	{
		$status = $status_row['status'];

		if( 'Y' == $status_row['isMms'] ) 
		{


			$photo_row = JWPicture::GetDbRowById( $status_row['idPicture'] );
			$user_row = JWDB_Cache_User::GetDbRowById( $status_row['idUser'] );

			$photo_title = $photo_row['fileName'];
			$photo_src = JWPicture::GetUrlById($status_row['idPicture'], 'middle');
			$photo_href = JW_SRVNAME .'/'. $user_row['nameUrl'] .'/mms/'. $status_row['id'];

			return array(
				'type' => 'html',
				'html' => '<a href="' .$photo_href. '" target="_blank"><img src="' .$photo_src. '" title="'.$photo_title.'" class="pic"/></a>',
			);

		}
		else if ( preg_match(	'#'
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

			foreach( self::$plugin_names as $plugin_name ) 
			{
				$callback = array('JWPlugins_' . $plugin_name , 'GetPluginResult');
				if ( is_callable( $callback ) ) 
				{
					$result = call_user_func( $callback, $url );
					if ( $result ) 
					{
						return $result;
					}
				}
			}
		}

		return array( 'type' => 'none' );
	}
}
