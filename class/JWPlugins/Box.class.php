<?php
class JWPlugins_Box
{

	static public function GetPluginResult( $string )
	{
		$info = self::GetPluginInfo( $string );
		if ( $info )
		{
				return array(
					'type' => 'html',
                    'html' => '<div style="margin:5px 0 5px 0;"><embed src="'.$info.'" quality="high" wmode="opaque" width="50" height="18" type="application/x-shockwave-flash" /></embed></div>',
					'types' => 'music',
					'src' => $info,
				);
		}

		return null;
	}

	static public function GetBoxId( $string )
	{
		if( preg_match('#http://(?:www\.|)8box\.c[n|om]([\/a-z\/]+)+([0-9]+)#i', $string, $matches))
		{
			return $matches[2];
		}
		else
		{
			return false;
		}

	}

	static public function GetPluginInfo( $string )
	{
		if( $box_id = self::GetBoxId( $string ) )
		{
			return self::BuildBoxUrl( $box_id );
		}

		return false;
	}

	static public function BuildBoxUrl( $box_id )
	{
		return 'http://www.8box.cn/feed/e92400_'.$box_id.'/p.swf';
	}

}
?>
