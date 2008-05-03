<?php

class JWPlugins_Flickr
{
    static private $api_key = '15445cd539d450cfc82a0297ff37db15';
    static private $api_url = 'http://api.flickr.com/services/rest/';

    static public function GetPluginResult( $string )
    {
        $info = self::GetPluginInfo( $string );

        if ( $info )
        {
            $src = self::BuildPhotoUrl( $info );
            $href = $string;
            return array(
                    'type' => 'html',
                    'html' => '<a href="' .$href. '" target="_blank" rel="nofollow"><img src="' .$src. '" title="Flickr图片" class="pic"/></a>',
					'types' => 'picture',
					'src' => $src,
                    );
        }
        return null;
    }


    static public function GetPluginInfo( $string )
    {
        if( false == preg_match( '#\.?flickr\.com/photos/([a-zA-Z0-9]+)/([0-9]+)/#i', $string, $matches ) )
            return false;

        $url = $matches[0];
        $user_name = $matches[1];
        $id = $matches[2];

        $mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWPlugins_Flickr', 'GetPhotoInfo'), array( $id ) );
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
        
        $v = self::GetPhotoInfoByApi( $id );
        if( false == empty( $v ) )
        {
            JWUrlMap::Create( null, $url, $v, array( 'type'=>'photo', ) );
            $memcache -> set( $mc_key, $v );
        }
        return $v;
        
    }
    
    static public function GetPhotoInfoByApi( $id )
    {
        $api_method = 'flickr.photos.getInfo';
        
        $params = array(
                'api_key'   => self::$api_key,
                'method'    => $api_method,
                'photo_id'  => $id,
                'format'    => 'php_serial',
                );
        $encoded_params = array();

        foreach ($params as $k => $v)
        {
            $encoded_params[] = urlencode($k).'='.urlencode($v);
        }
        $rpc_url = self::$api_url.'?'.implode('&', $encoded_params);

        $rsp = file_get_contents( $rpc_url );

        $rsp_obj = @unserialize( $rsp );

	if ( null==$rsp_obj || 'fail'==@$rsp_obj['stat'] || 0==@$rsp_obj['photo']['visibility']['ispublic'] || 2>=@$rsp_obj['photo']['farm'] )
		return array();

        return array(
            'photo_server' => $rsp_obj['photo']['server'],
            'photo_id'  => $rsp_obj['photo']['id'],
            'photo_secret' => $rsp_obj['photo']['secret'],
            'photo_farm' => $rsp_obj['photo']['farm'],
        );
    }

    static public function BuildPhotoUrl( $photo_info )
    {
        if( false == empty( $photo_info ) )
        {
            return 'http://farm'.$photo_info['photo_farm'].'.static.flickr.com/'.$photo_info['photo_server'].'/'.$photo_info['photo_id'].'_'.$photo_info['photo_secret'].'_m.jpg';
        }
        else
            return null;
    }


}

?>
