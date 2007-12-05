<?php
class JWPlugins_Box
{

    static public function GetBoxId( $string )
    {
        if( preg_match('#http://www\.8box\.c[n|om]([\/a-z\/]+)+([0-9]+)#i', $string, $matches))
        {
            return $matches[2];
        }
        else
        {
            return false;
        }

    }
    
    static public function GetBoxInfo( $string )
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
