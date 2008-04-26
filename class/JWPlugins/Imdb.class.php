<?php

class JWPlugins_Imdb {

    static private $apikey = '91395a28d87577ecb3ac0e7a896067c4';

    static public function GetPluginResult( $string ) {
        $info = self::GetPluginInfo( $string );
        $link = $info['link'];
        $title = htmlspecialchars($info['title']);
        $image = $info['image'];
        $author = implode(',', $info['author']);
        $extra = @$info['extra'];

        if ( is_array($extra) ) {
            if (array_key_exists('country', $extra))
                $tag.='<p class="t-text">出品国家：'.implode('/', $extra['country']).'</p>';
            if (array_key_exists('pubdate', $extra))
                $tag.='<p class="t-text">发行时间：'.implode('/', $extra['pubdate']).'</p>';
            if (array_key_exists('language', $extra))
                $tag.='<p class="t-text">主要语言：'.implode('/', $extra['language']).'</p>';
            if (array_key_exists('cast', $extra))
                $tag.='<p class="t-text">主要演员：'.implode('/',$extra['cast']).'</p>';
        }

        if ( $info ) {
            if (empty($image))
                $html = <<<__DOUBAN__
                    <div style="padding:10px; margin:5px; border:1px dashed #CCC;">
                    <p class="t-text">$title</p>
                    $tag
                    </div>
__DOUBAN__;
            else 
                $html = <<<__DOUBAN__
                    <div style="padding:10px; margin:5px; border:1px dashed #CCC;">
                    <table width="100%"><tr>
                    <td valign="top" halign="left">
                    <a href="$link" targe="_blank"><img src="$image" title="$title" alt="$title"/></a>
                    </td>
                    <td valign="top" halign="right">
                    <p class="t-text">$title ($author)</p>
                    $tag
                    </td>
                    </tr></table>
                    </div>
__DOUBAN__;
            return array(
                    'type' => 'html',
                    'html' => $html,
                    );
		}
        return null;
    }

    static public function GetPluginInfo( $string ) {
        if (false == preg_match('#\.?imdb\.com/title/(tt\d+)#', $string, $matches) )
            return false;

        $url = $matches[0];
        $id = $matches[1];

        $mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWPlugins_Imdb', 'GetImdbInfo'), array( $id ) );
        $memcache = JWMemcache::Instance();

        $v = $memcache -> Del( $mc_key );
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

        $v = self::GetImdbInfoByApi( $id );
        if( false == empty( $v ) )
        {
            JWUrlMap::Create( null, $url, $v, array( 'type'=>'mix', ) );
            $memcache -> set( $mc_key, $v );
        }
        return $v;
        
    }

    static public function GetImdbInfoByApi( $id ) {
        $assoc =  null;
        $url = 'http://api.douban.com/movie/subjects?q='.$id.'&apikey='.self::$apikey.'&alt=json';
        $assoc = json_decode(file_get_contents($url), true);
        $url = @$assoc['entry'][0]['id']['$t'];
        $url.= '?apikey='.self::$apikey.'&alt=json';
        $assoc = json_decode(file_get_contents($url), true);

        if (!is_array($assoc)) return false;

        $author = $link = $image = $title = null;
        foreach ($assoc['link'] as $node) {
            switch ($node['@rel']) {
                case 'alternate' :
                    $link = $node['@href']; break;
                case 'image':
                    $image = $node['@href']; break;
            }
        }

        foreach ($assoc['author'] as $node) {
            $author[] = $node['name']['$t'];
        }

        foreach ($assoc['db:attribute'] as $node) {
            $extra[$node['@name']][] = $node['$t'];
        }

        return array(
            'category'  => $cat,
            'author'=> $author,
            'title' => $assoc['title']['$t'],
            'link'  => $link,
            'image' => $image,
            'extra' => $extra,
        );
    }

}

?>

