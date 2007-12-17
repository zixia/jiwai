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

			switch( $source )
			{
				case 'tudou':
					$html = '<div style="border:1px solid #999999; background-color:#000000; padding:4px; width:300px; margin:5px 0 0px 0;"> <object width="300" height="225"><param name="movie" value="'.$id.'"></param><param name="allowScriptAccess" value="always"></param><param name="wmode" value="opaque"></param><embed src="http://www.tudou.com/v/'.$id.'" type="application/x-shockwave-flash" width="300" height="225" allowFullScreen="true" wmode="opaque" allowScriptAccess="always"></embed></object></div>';
				break;
				case 'youku':
					$html = '<div style="border:1px solid #999999; background-color:#000000; padding:4px; width:300px; margin:5px 0 0px 0;"><embed src="http://player.youku.com/player.php/sid/'.$id.'" quality="high" width="300" height="248" align="middle" allowScriptAccess="sameDomain" wmode="opaque" type="application/x-shockwave-flash"></embed></div>';
				break;
				case 'youtube':
					$html = '<div style="border:1px solid #999999; background-color:#000000; padding:4px; width:300px; margin:5px 0 0px 0;"><object width="425" height="355"><param name="movie" value="http://www.youtube.com/v/'.$id.'"></param><param name="wmode" value="opaque"></param><embed src="http://www.youtube.com/v/'.$id.'&rel=1" type="application/x-shockwave-flash" wmode="opaque" width="300" height="251"></embed></object></div>';
				break;
			}

			return array(
				'type' => 'html',
				'html' => $html,
			);
		}

		return null;
	}

    static public function Intercept( $string )
    {
        if( preg_match('#http://(www\.tudou\.com)/programs/view/([a-zA-Z0-9_]+)#i',$string) 
			|| preg_match('#http://(v\.youku\.com)/v\_show/id\_([a-zA-Z0-9]+)#i',$string ) 
			|| preg_match('#http://([www\.]*youtube\.com)/watch\?v\=([a-zA-Z0-9_]+)#i',$string ))
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

        if( preg_match('#http://(?:www\.|)tudou\.com/programs/view/([a-zA-Z0-9_]{11})#i',$string,$matches))
        {
            return array(
                'id' => $matches[1],
                'source' => 'tudou',
            );
        }
        if( preg_match('#http://(?:www\.|)youtube\.com/v/([a-zA-Z0-9_]{11})#i',$string,$matches))
        {
            return array(
                'id' => $matches[1],
                'source' => 'youtube',            
            );

        }
        if( preg_match('#http://(?:www\.|)tudou\.com/v/([a-zA-Z0-9_]{11})#i',$string,$matches))
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

        if(preg_match('#http://([www\.]*youtube\.com)/watch\?v\=([a-zA-Z0-9_]{11})#i',$string,$matches))
        {
            return array(
                'id' => $matches[2],
                'source' => 'youtube',
            );
        }

        return false;
    }
}
?>
