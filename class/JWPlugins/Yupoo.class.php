<?php
/**
 * @package JiWai
 * @author kxp@jiwai.de
 * @version $Id$
 */

class JWPlugins_Yupoo{

	static private $apiUrl = "http://www.yupoo.com/api/rest/";
	static private $apiKey = "7faa1a2a800f340d3da47912e13a1fa6";

	/**
	 * @author seek
	 */
	static public function GetPluginResult( $string )
	{
		$info = self::GetPluginInfo( $string );
		if ( $info )
		{
			$src_small = 'http://photo'.$info['host'].'.yupoo.com/'.$info['dir'].'/'.$info['filename'].'/small/';
			$src_size = ""; 
			$src_small_info = array();
			if("gif"!=$info['originalformat'])
				$src = $src_small;
			else
			{   
				$src = self::BuildPhotoUrl( $info );
				$src_small_info = getimagesize( $src_small );
			}   

			if( !empty($src_small_info) )
				$src_size = ' width="'.$src_small_info[0].'" height="'.$src_small_info[1].'"';

			$href = $string;
			return array(
					'type' => 'html',
					'html' => '<div class="e_photo e_photo_yupoo"><a href="' .$href. '" target="_blank"><img'.$src_size.' src="' .$src. '" title="Yupoo图片" class="pic"/></a></div>',
					'types' => 'picture',
					'src' => $src,
				    );  
		}
		return null;
	}

	/** 
	 * Intecept 
	 **/
	static public function Intercept( $string, &$objectId="" ) 
	{
		if(preg_match('#http://www\.yupoo\.com/photos/view\?id=([0-9a-f]+)#i', $string, $matches))
		{
			return $matches;
		}
		else
		{
			return false;
		}
	}


	/**
	 * @author wqsemc@jiwai.com 
	 */
	static public function GetPluginInfo( $string ) 
	{
		if( false == preg_match('#\.?yupoo\.com/(.*)[\?|&]id=([0-9a-f]{32})#i', $string, $matches))
			return false;

		$url = $matches[0];
		$id = $matches[2];

		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWPluglins_Yupoo', 'GetPhotoInfo' ), array($id));
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( $v ) 
			return $v;

		$url_row = JWUrlMap::GetDbRowByDescUrl($url);

		if( false==empty($url_row) ) 
		{
			$v = $url_row['metaInfo'];
			$memcache->set( $mc_key, $v );
			return $v;
		}

		$v = self::GetPhotoInfoByApi( $id );

		if( false==empty($v) ) 
		{
			JWUrlMap::Create( null, $url, $v, array( 'type'=>'photo', ) );
			$memcache->set( $mc_key, $v );
		}
		return $v;
	}

	static public function GetPhotoInfoByApi( $photoId )
	{

		$apiMethod = "yupoo.photos.getInfo";

		$rpcUrl = self::$apiUrl . "?method=$apiMethod&api_key=".self::$apiKey."&photo_id=$photoId";

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $rpcUrl);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010);
		$data = curl_exec($ch);
		curl_close($ch);
		$xmlObject = simplexml_load_string( $data );

		if( $xmlObject && !empty($xmlObject) ) 
		{
			$photoNode = $xmlObject->photo;
			if ( null===$photoNode )
				return null;

			$secret = $host = $dir = $filename = $originalformat = null;
			foreach( $photoNode->attributes() as $k=>$n ) {
				$$k = (string)$n;
			}

			if( $secret && $dir && $filename && $originalformat ) 
			{
				return array(
						'secret' => $secret,
						'host' => $host,
						'dir' => $dir,
						'filename' => $filename,
						'originalformat' => $originalformat,
					    );
			}
		}

		return null;
	}

	static public function BuildPhotoUrl( $photoInfo ) 
	{
		if ( false == empty($photoInfo) )
			return 'http://photo'.$photoInfo['host'].'.yupoo.com/'.$photoInfo['dir'].'/'.$photoInfo['filename'].'/medium/';
		else
			return null;
	}
}
?>
