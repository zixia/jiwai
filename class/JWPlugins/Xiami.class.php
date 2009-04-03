<?php

/**
 * $Id$
 */ 

class JWPlugins_Xiami
{

	static public function GetPluginResult( $string )
	{
		$info = self::GetPluginInfo( $string );
		if ( $info )
		{
				return array(
					'type' => 'html',
                    'html' => '<div style="margin:5px 0 5px 0;"><embed src="'.$info.'" quality="high" wmode="transparent" width="257" height="33" type="application/x-shockwave-flash" /></embed></div>',
					'types' => 'music',
					'src' => $info,
				);
		}

		return null;
	}

    static private function GetEmbeddedSwfBySongId($id) {
        $id = JWDB::CheckInt($id);
        return 'http://www.xiami.com/widget/103159_'. $id . '/singlePlayer.swf';
    }

    static public function GetPluginInfo( $string )
    {
        if (preg_match('#(http://(?:www\.|)xiami\.com/widget/\d+_\d+/singlePlayer.swf)#i',  $string, $matches)) {
            return $matches[1];
        } if (preg_match('#http://(?:www\.|)xiami\.com/song/(\d+)#i', $string, $matches)) {
            return self::GetEmbeddedSwfBySongId($matches[1]);
        }
        return false;
    }
}
?>

