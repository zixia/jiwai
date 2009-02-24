<?php
/**
 * @author: seek@jiwai.com
 */
class JWUtility {

	static private $global_vars = array();

	static public function Option($a=array(), $v=null, $all=null)
	{
		$option = null;
		if ( $all ){
			$selected = ($v) ? null : 'selected';
			$option .= "<option value='' $selected>$all</option>";
		}

		$v = explode(',', $v);
		settype($v, 'array');
		foreach( $a AS $key=>$value )
		{
			$selected = in_array($key, $v) ? 'selected' : null;
			$option .= "<option value='$key' $selected>$value</option>";
		}
		
		return $option;
	}

	static public function SortArray($a=array(), $s=array(), $key=null)
	{
		if ( $key ) $a = self::GetColumn($a, $key, false);
		$ret = array();
		foreach( $s AS $one ) 
		{
			if ( isset($a[$one]) )
				$ret[$one] = $a[$one];
		}
		return $ret;
	}

	static public function GetColumn($a=array(), $column='id', $null=true)
	{
		$ret = array();
		foreach( $a AS $one )
		{   
			if ( $null || @$one[ $column ] )
				$ret[] = @$one[ $column ];
		}   

		return $ret;
	}

	/* support 2-level now */
	static public function AssColumn($a=array(), $column='id')
	{
		$two_level = func_num_args() > 2 ? true : false;
		if ( $two_level )
			$scolumn = func_get_arg(2);

		$ret = array();
		if ( false == $two_level )
		{   
			foreach( $a AS $one )
			{   
				$ret[ @$one[$column] ] = $one;
			}   
		}   
		else
		{   
			foreach( $a AS $one )
			{   
				if ( false==isset( $ret[ @$one[$column] ] ) )
					$ret[ @$one[$column] ] = array();

				$ret[ @$one[$column] ][ @$one[$scolumn] ] = $one;
			}
		}
		return $ret;
	}

	static public function GetAstro($birthday='0000-00-00') {
		list($year, $month, $day) = explode('-', $birthday);
		$date = strtotime('1999-'.$month.'-'.$day);
		$astros = array(
				array("摩羯座",strtotime('1999-00-01')),
				array("水瓶座",strtotime('1999-00-20')),
				array("双鱼座",strtotime('1999-01-19')),
				array("白羊座",strtotime('1999-02-21')),
				array("金牛座",strtotime('1999-03-21')),
				array("双子座",strtotime('1999-04-21')),
				array("巨蟹座",strtotime('1999-05-22')),    
				array("狮子座",strtotime('1999-06-23')),
				array("处女座",strtotime('1999-07-23')),
				array("天秤座",strtotime('1999-08-23')),
				array("天蝎座",strtotime('1999-09-23')),
				array("射手座",strtotime('1999-10-22')),
				array("摩羯座",strtotime('1999-11-22')), 
			       );

		for($i=12;$i>=0;$i--){
			if ($date >= $astros[$i][1]) 
				return $astros[$i][0]; 
		}
	}

	static public function AddLink($string) {
		$pattern = '#^(.*?)(http|https|ftp|rtsp|mms)?://([\x00-\x1F\x21-\x2B\x2D-\x2E\x30-\x39\x3B-\x7F]+)([\x00-\x09\x0B-\x0C\x0E-\x1F\x21-\x7F\xE0-\xEF\x80-\xBF]*)(.*)$#is';
		if ( preg_match($pattern, $string, $matches) ) {

			$head_str = $matches[1];
			$url_scheme = $matches[2];
			$url_domain = $matches[3];
			$url_path = $matches[4];
			$tail_str = $matches[5];

			if ( preg_match( '#jiwai.de#i', $url_domain ) ) {
				$string = "{$head_str}<a target=\"_blank\" href=\"{$url_scheme}://{$url_domain}{$url_path}\">{$url_scheme}://{$url_domain}{$url_path}</a>{$tail_str}";
			}
		}
		return $string;
	}

	static public function GetRssLink() {

		if ( self::$global_vars['getrsslink'] ) 
			return self::$global_vars['getrsslink'];

		global $g_page_user_id;

		$current_user_id = JWLogin::GetCurrentUserId();
		$uri = @$_SERVER['REQUEST_URI'];
		$prehost = "http://api.{$_SERVER['HTTP_HOST']}/statuses";

		$rss = array();
		if (empty($uri) ) return $rss;

		//self
		if ( preg_match('#/wo/#', $uri) && $current_user_id ) {
			$link = "{$prehost}/user_timeline/{$current_user_id}.rss";
			$rss[$link] = "自己的源";
		}
		//tag
		if (preg_match('#/t/([^/]+)#i', $uri, $m) ) {
			$tag_name = urlDecode($m[1]);
			$tag_id = JWTag::GetIdByNameOrCreate($tag_name);
			$link = "{$prehost}/channel_timeline/{$tag_id}.rss";
			$rss[$link] = "订阅[{$tag_name}]";
		}
		//g
		elseif (preg_match('#/g/#', $uri) ) {}

		//public_timeline
		if (preg_match('#^/public_timeline/#i', $uri) ) {
			$link = "{$prehost}/public_timeline.rss";
			$rss[$link] = "订阅最新叽歪";
		}
		//user
		elseif (preg_match('#^/([^/]{3,})/(with_friends|favourites)?#i', $uri, $m) ) {
			$user = JWUser::GetUserInfo($g_page_user_id);
			$link = "{$prehost}/user_timeline/{$g_page_user_id}.rss";
			$rss[$link] = "订阅{$user['nameScreen']}";
			$step = strtolower(@$m[2]);
			switch($step) {
				case 'with_friends':
					$link = "{$prehost}/friends_timeline/{$g_page_user_id}.rss";
					$rss[$link] = "订阅{$user['nameScreen']}与朋友";
					break;
			}
		}

		return self::$global_vars['getrsslink'] = $rss;
	}

	static public function HighLight($string=null) {
		if (!(preg_match('#/search/#', @$_SERVER['REQUEST_URI']) 
					&& $key=@$_GET['q']))
			return $string;

		$key = strtolower($key);
		$key = preg_replace('/[\#]+/', '', $key);
		$keys = preg_split('/[\s,\+\-\(\)\#\/\\\\\*]+/', $key);
		$keys = array_diff(array_unique($keys), array('and','or','not'));
		$pattern = implode('|', $keys);
		$string_orin = $string;
		
		$string_on = strip_tags($string);
		$string_on = preg_replace("/({$pattern})/i", "<font color=\"red\">\\1</font>", $string_on);
		$string = preg_replace("/({$pattern})/i", "<font color=\"red\">\\1</font>", $string);
		$string = preg_replace("/(<[^<>]+)<font color=\"red\">({$pattern})<\/font>([^<>]+>)/i", "\\1\\2\\3", $string);
		return strip_tags($string_on)==strip_tags($string) 
			? $string : $string_orin;
	}

	static public function GenCrumb() {
		$user_id = JWLogin::GetCurrentUserId();
		$secret = uniqid();
		$crumb = md5("{$user_id}={$uniqid}");
		$session_id = session_id();
		$memcache = JWMemcache::Instance('default');
		$memcache->Set($crumb, $session_id, 0, 1800);
		return $crumb;
	}

	static public function CheckCrumb($crumb=null) {
		$crumb = (null==$crumb) ? @$_REQUEST['crumb'] : $crumb;
		$memcache = JWMemcache::Instance('default');
		$correct = ( $memcache->Get($crumb) == session_id() );
		$memcache->Del($crumb);
		if ( false === $correct ) {
			JWSession::SetInfo('notice', '叽歪判定为非法请求，请重新尝试。');
			JWTemplate::RedirectBackToLastUrl('/');
		}
		return $correct;
	}

	static public function MustPost() {
		if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
			JWTemplate::RedirectBackToLastUrl('/');
		}
	}
}
?>
