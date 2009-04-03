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
			'Picasa',
			'Douban',
			'Imdb',
			'TeX',
			);

	static private $plugin_domains = array(
			'yupoo.com' => 'Yupoo',
			'8box.cn'   => 'Box',
            'xiami.com' => 'Xiami',
			'yobo.com'  => 'Yobo',
			'flickr.com'    => 'Flickr',
			'google.com'    => 'Picasa',
			'youtube.com'   => 'Video',
			'youku.com'     => 'Video',
			'tudou.com'     => 'Video',
			'vimeo.com'     => 'Video',
			'douban.com'    => 'Douban',
			'tug.org'   => 'TeX',
			);

	static public function GetPluginResult( $status_row, $scale='middle' )
	{
		$status = $status_row['status'];

		if( 'MMS' == $status_row['statusType'] ) 
		{
			$photo_row = JWPicture::GetDbRowById( $status_row['idPicture'] );
			$user_row = JWDB_Cache_User::GetDbRowById( $status_row['idUser'] );

			$photo_title = $photo_row['fileName'];
			$photo_src = JWPicture::GetUrlById($status_row['idPicture'],$scale);
			$photo_href = str_replace("/$scale/",'/origin/',$photo_src);

			return array(
					'type' => 'html',
					'html' => '<div class="e_photo e_photo_mms"><a href="' .$photo_href. '" target="_blank"><img src="' .$photo_src. '" title="'.$photo_title.'" class="pic"/></a></div>',
					'types' => 'picture',
					'src' => $photo_src,
				    );

		}
		else if ( preg_match('/isbn[^\w\d]*(\d{13})/i', $status, $matches) )
		{
			$callback = array('JWPlugins_Douban', 'GetPluginResult');
			if ( is_callable( $callback ) ) 
			{
				$result = call_user_func( $callback, '.douban.com/isbn/' . $matches[1] );
				if ( $result ) 
				{
					return $result;
				}
			}
		}
		else if ( preg_match('/imdb[^\w\d]*(tt\d{7})/i', $status, $matches) )
		{
			$callback = array('JWPlugins_Imdb', 'GetPluginResult');
			if ( is_callable( $callback ) ) 
			{
				$result = call_user_func( $callback, '.imdb.com/title/' . $matches[1] );
				if ( $result ) 
				{
					return $result;
				}
			}
		}
		else if ( preg_match('#(\[tex\].*?\[/tex\])#i', $status, $matches) )
		{
			$callback = array('JWPlugins_TeX', 'GetPluginResult');
			if ( is_callable( $callback ) ) 
			{
				$result = call_user_func( $callback, $matches[1] );
				if ( $result ) 
				{
					return $result;
				}
			}
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

			$url_domain_strip = strtolower($url_domain);
			$url_domain_strip = preg_replace('/^.*?([^\.]+\.[^\.]+)$/', '${1}', $url_domain_strip);
			if ( array_key_exists($url_domain_strip, self::$plugin_domains) )
			{
				$plugin_name = self::$plugin_domains[$url_domain_strip];
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
