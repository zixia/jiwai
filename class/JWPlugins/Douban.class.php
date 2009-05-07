<?php

class JWPlugins_Douban {

    static private $apikey = '91395a28d87577ecb3ac0e7a896067c4';

    static private $guess = array('book', 'movie', 'music');

    static private $apiurl = array(
            'isbn'  => 'http://api.douban.com/book/subjects?q=',
            'imdb'  => 'http://api.douban.com/movie/subjects?q=',
            'book'  => 'http://api.douban.com/book/subject/',
            'movie' => 'http://api.douban.com/movie/subject/',
            'music' => 'http://api.douban.com/music/subject/',
            'review'=> 'http://api.douban.com/review/',
            'people'=> 'http://api.douban.com/people/',
            'event' => 'http://api.douban.com/event/',
            );

    static private function generateApiUrl($id, $cat) {
        if (! array_key_exists($cat, self::$apiurl)) return null;
        if ('isbn' == $cat || 'imdb' == $cat)
            return self::$apiurl[$cat].urlencode($id).'&apikey='.self::$apikey.'&alt=json';
        return self::$apiurl[$cat].urlencode($id).'?apikey='.self::$apikey.'&alt=json';
    }

    static private function renderOutput($url, $cat = 'subject') {
        $raw = @file_get_contents($url);
        $ret = json_decode($raw, true);
        if (! is_array($ret))
            return null;
        if ('isbn' == $cat || 'imdb' == $cat)
            return $ret['entry'][0];
        return $ret;
    }

    private static function validate($id, $cat) {
        $regex_isbn = '/^\d{13}$/';
        $regex_isbn_10 = '/^\d{10}$/';
        $regex_subject = '/^\d+$/';
        $regex_imdb = '/^tt\d+$/';

        switch ($cat) {
            case 'isbn' :
                if (preg_match($regex_isbn, $id)) return true;
            case 'isbn_10' :
                return preg_match($regex_isbn_10, $id);
            case 'imdb' :
                return preg_match($regex_imdb, $id);
            case 'subject' :
            default :
                return preg_match($regex_subject, $id);
        }

        return false;
    }

    static public function GetPluginResult( $string ) {
        $info = self::GetPluginInfo( $string );
        $summary = @$info['summary']
            ? htmlspecialchars($info['summary'])
            : '暂无介绍';
        $link = $info['link'];
        $title = htmlspecialchars($info['title']);
        $image = $info['image'];
        $author = @$info['author']
            ? is_array($info['author']) ? implode(',',$info['author']) : $info['author']
            : '叽歪不知道是谁';

        if ( $info ) {
            if (empty($image))
                $html = <<<__DOUBAN__
                    <div style="padding:10px; margin:5px; border:1px dashed #CCC;">
                    <p class="t-text">$title</p>
                    <p class="t-text">$summary</p>
                    </div>
__DOUBAN__;
            else 
                $html = <<<__DOUBAN__
                    <div style="padding:10px; margin:5px; border:1px dashed #CCC;">
                    <table width="100%"><tr>
                    <td valign="top" halign="left">
                    <p class="t-text">$title ($author)</p>
                    <p class="t-text">$summary</p>
                    </td>
                    <td valign="top" halign="right">
                    <a href="$link" targe="_blank"><img src="$image" title="$title" alt="$title"/></a>
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
        if (false == preg_match('#\.?douban\.com/(\w+)/([\w\d]+)#', $string, $matches) )
            return false;

        $url = $matches[0];
        $cat = $matches[1];
        $id = $matches[2];

        $mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWPlugins_Douban', 'GetDoubanInfo'), array( $cat, $id ) );
        $memcache = JWMemcache::Instance();

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

        $v = self::GetDoubanInfoByApi( $id, $cat );
        if( false == empty( $v ) )
        {
            JWUrlMap::Create( null, $url, $v, array( 'type'=>'mix', ) );
            $memcache -> set( $mc_key, $v );
        }
        return $v;
        
    }

    static public function GetDoubanInfoByApi( $id, $cat ) {
        $assoc =  null;
        if ('isbn' == $cat || self::validate($id, 'isbn')) {
            $cat = 'isbn';
            $url = self::generateApiUrl($id, $cat);
            $assoc = self::renderOutput($url, $cat);
            $url = $assoc['id']['$t'].'?apikey='.self::$apikey.'&alt=json';
            $assoc = self::renderOutput($url);
        } elseif('imdb' == $cat || self::validate($id, 'imdb')) {
            $cat = 'imdb';
            $url = self::generateApiUrl($id, $cat);
            $assoc = self::renderOutput($url, $cat);
            $url = $assoc['id']['$t'].'?apikey='.self::$apikey.'&alt=json';
            $assoc = self::renderOutput($url);
        } elseif ('subject' == $cat) {
            foreach (self::$guess as $maybe) {
                $cat = $maybe;
                $url = self::generateApiUrl($id, $cat);
                if (null == $url) return false;
                $assoc = self::renderOutput($url, $cat);
                if (is_array($assoc)) break;
            }
        } else {
            $url = self::generateApiUrl($id, $cat);
            $assoc = self::renderOutput($url, $cat);
        }

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

        if ( isset($assoc['author']['name']['$t']) ) {
            $author = $assoc['author']['name']['$t'];
        } else {
            foreach ($assoc['author'] as $node) {
                if ( isset($node['name']) )
                    $author[] = $node['name']['$t'];
            }
        }

        return array(
            'category'  => $cat,
            'author'=> $author,
            'title' => $assoc['title']['$t'],
            'link'  => $link,
            'image' => $image,
            'summary'   => @$assoc['summary']['$t']
            ? $assoc['summary']['$t']
            : $assoc['summary'][0]['$t'],
        );
    }

}

?>

