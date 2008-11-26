<?php
class JWPlugins_TeX
{
    static public function GetPluginResult( $string )
    {
        $info = self::GetPluginInfo( $string );
        if ( $info )
        {
            $equation = htmlspecialchars($info);
            $html = <<<__TeX__
                    <div style="padding:10px; margin:5px; border:1px dashed #CCC;">
                    <img src="http://www.codecogs.com/eq.latex?${equation}" />
                    </div>
__TeX__;
            return array(
                    'type' => 'html',
                    'html' => $html,
                    'types' => 'mix',
                    );
            return null;
        }
    }

    static public function GetPluginInfo( $string )
    {
        if( preg_match('#\[tex\](.*?)\[/tex\]#i', $string, $matches))
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

