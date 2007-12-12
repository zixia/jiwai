<?php
class JWPlugins_Yobo
{

	static public function GetPluginResult( $string )
	{
		$info = self::GetPluginInfo( $string );
		if ( $info )
		{
			$rand_num = rand(1,24);
			$rand_num = ( $rand_num < 10 ) ? '0' . $rand_num : $rand_num;

			return array(
					'type' => 'html',
            		'html' => '<div style="margin:5px 0 5px 0;"><embed src="http://www.yobo.com/flash/w/105/singlemusic.swf" FlashVars="P=GW|www.yobo.com|xmlUrl|'. $info.'|autoPlay|0|volume|80|skin|'. $rand_num.'|w=255" quality="high" bgcolor="#ffffff" width="255" height="108" align="middle" type="application/x-shockwave-flash"> </embed><br /><a href="http://www.yobo.com" target="_blank"><img src="http://www.yobo.com/images/blank_image.gif" style="display:none;" title="YOBO友播,音乐DNA,音乐推荐,排行榜,音乐插件,MUSIC WIDGET" /></a></div>',
			);
			return null;
		}
	}

    static public function GetPluginInfo( $string )
    {
        if( preg_match('#http://www\.yobo\.com/song/view/([0-9]+)#i', $string, $matches))
        {
            return $matches[1];
        }
        else
        {
            return false;
        }
    }
}

?>
