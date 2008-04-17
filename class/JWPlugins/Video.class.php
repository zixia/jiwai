<?php

class JWPlugins_Video
{

	static public function GetPluginResult( $string )
	{
		$info = self::GetPluginInfo( $string );

		if ( $info )
		{
			$source = $info['source'];
			$id = $info['id'];
			$html = null;
			$src = null;

			switch( $source )
			{
                case 'tudou':
                    $html = '<div style="border:1px solid #999999; background-color:#000000; padding:4px; width:300px; margin:5px 0 0px 0;"><embed src="http://www.tudou.com/v/'.$id.'" quality="high" width="300" height="225" align="middle" allowScriptAccess="sameDomain" wmode="opaque" type="application/x-shockwave-flash"></embed></div>';
                    $src = "http://www.tudou.com/v/$id";
                break;
                case 'youku':
                    $html = '<div style="border:1px solid #999999; background-color:#000000; padding:4px; width:300px; margin:5px 0 0px 0;"><embed src="http://player.youku.com/player.php/sid/'.$id.'" quality="high" width="300" height="248" align="middle" allowScriptAccess="sameDomain" wmode="opaque" type="application/x-shockwave-flash"></embed></div>';
                    $src = "http://player.youku.com/player.php/sid/$id";
                break;
                case 'youtube':
                    $html = '<div style="border:1px solid #999999; background-color:#000000; padding:4px; width:300px; margin:5px 0 0px 0;"><embed src="http://www.youtube.com/v/'.$id.'" quality="high" width="300" height="251" align="middle" allowScriptAccess="sameDomain" wmode="opaque" type="application/x-shockwave-flash"></embed></div>';
                    $src = "http://www.youtube.com/v/$id";
                break;
			}

			return array(
				'type' => 'html',
				'html' => $html,
				'types' => 'video',
				'src' => $src,
			);
		}

		return null;
	}

    static public function Intercept( $string )
    {
        if( preg_match('#http://(?:www\.|)tudou\.com/programs/view/([a-zA-Z0-9_-]+)#i', $string) 
			|| preg_match('#http://(v\.youku\.com)/v\_show/id\_([a-zA-Z0-9]+)#i', $string ) 
			|| preg_match('#http://(?:www\.|)youtube\.com/watch\?v\=([a-zA-Z0-9_]+)#i', $string )
            || preg_match('#http://(?:www\.|)tudou\.com/v/([a-zA-Z0-9_-]+)#i', $string )
            || preg_match('#http://(player\.youku\.com)/player.php/sid/([a-zA-Z0-9_]+)#i', $string )
            || preg_match('#http://(?:www\.|)youtube\.com/v/([a-zA-Z0-9_]+)#i', $string ))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static public function GetPluginInfo( $string )
    {
        if( preg_match('#http://(v\.youku\.com)/v\_show/id\_([a-zA-Z0-9]+)#i',$string,$matches))
        {
            return array(
                'id' => $matches[2],
                'source' => 'youku',
            );
            
        }

        if( preg_match('#http://(?:www\.|)tudou\.com/programs/view/([a-zA-Z0-9_-]+)#i',$string,$matches))
        {
            return array(
                'id' => $matches[1],
                'source' => 'tudou',
            );
        }
        if( preg_match('#http://(?:www\.|)youtube\.com/v/([a-zA-Z0-9_]+)#i',$string,$matches))
        {
            return array(
                'id' => $matches[1],
                'source' => 'youtube',            
            );

        }
        if( preg_match('#http://(?:www\.|)tudou\.com/v/([a-zA-Z0-9_-]+)#i',$string,$matches))
        {
            return array(
                'id' => $matches[1],
                'source' => 'tudou',
                
            );
        }
        if( preg_match('#http://(player\.youku\.com)/player.php/sid/([a-zA-Z0-9_]+)#i',$string,$matches))
        {
            return array(
                'id' => $matches[2],
                'source' => 'youku',
            );
        }

        if(preg_match('#http://(?:www\.|)youtube\.com/watch\?v\=([a-zA-Z0-9_]+)#i',$string,$matches))
        {
            return array(
                'id' => $matches[1],
                'source' => 'youtube',
            );
        }

        return false;
    }
}
?>
