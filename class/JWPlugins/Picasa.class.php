<?php
class JWPlugins_Picasa
{
	static public function GetPluginResult( $string )
	{
		$info = self::GetPluginInfo( $string );

		if ( $info )
		{
			$src = self::BuildPhotoUrl( $info );
			$href = $string;
			return array(
					'type' => 'html',
					'html' => '<div class="e_photo e_photo_picasa"><a href="' .$href. '" target="_blank" rel="nofollow"><img src="' .$src. '" title="Picasa图片" class="pic"/></a></div>',
					'types' => 'picture',
					'src' => $src,
				    );
		}
		return null;
	}


	static public function GetPluginInfo( $string )
	{
		if (false==preg_match('#http://picasaweb\.google\.com/([^\/]+)/([^\#\/]+)\#(\d+)#', $string, $matches))
			return false;

		$url = trim($matches[0]);
		$user_name = $matches[1];
		$album_name = $matches[2];
		$photo_id = $matches[3];

		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWPlugins_Picasa', 'GetPhotoInfo'), array( $photo_id ) );
		$memcache = JWMemcache::Instance();

		$memcache -> set( $mc_key, array() );
		$v = $memcache -> Get( $mc_key );

		if( $v )
			return $v;

		$url_row = JWUrlMap::GetDbRowByDescUrl( $url );
		if( false == empty( $url_row ) )
		{
			$v = $url_row['metaInfo'];
			$memcache -> set( $mc_key, $v );
			return $v;
		}

		$v = self::GetPhotoInfoByApi( $url, $photo_id );
		if( false == empty( $v ) )
		{
			JWUrlMap::Create( null, $url, $v, array( 'type'=>'photo', ) );
			$memcache -> set( $mc_key, $v );
		}
		return $v;

	}

	static public function GetPhotoInfoByApi( $url, $id )
	{
		$content = @file_get_contents($url);
		$content = preg_replace('/[\r\n\s]+/', ' ', $content);
		$content = preg_match('#'.$id.'(.+)content\$src":"([^"]+)"(.+)'.$id.'#i', $content, $m);
		if ( $m ) {
			return $m[2];
		}
		return false;
	}

	static public function BuildPhotoUrl( $photo_info )
	{
		return preg_replace('#/([^/]+)\.(\w+)$#', "/s320/\\1.\\2", $photo_info);
	}
}

?>
