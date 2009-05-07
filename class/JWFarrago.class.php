<?php
/**
 * @package JiWai.de
 * @copyright AKA Inc.
 * @author seek@jiwai.com
 * @version $Id$
 */

/**
 * JiWai.de JWFarrago.class.php
 */
class JWFarrago
{
	const ID_USER_ESCREEN = 165657;
	const SIZE_TRENDWORD = 50;

	static public function GetEScreen($size=3, $offset=0) 
	{
		$esuser_id = self::ID_USER_ESCREEN;
		$status_data = JWDB_Cache_Status::GetStatusIdsFromUser($esuser_id, $size, $offset);
		$status_rows = JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);
		$events = array();
		foreach( $status_rows AS $one ) {
			list($e, $u, $t, $a) = preg_split('/(:|：)/', $one['status'], 4);
			$u = JWUser::GetUserInfo($u);
			$events[] = array(
					'event' => $e,
					'user' => $u,
					'time' => $t,
					'address' => $a,
					);
		}
		return $events;
	}

	static public function GetHotWords($size=100, $offset=0) 
	{
		$maxnum = 200;
		$offset = $offset > $maxnum ? 0 : $offset;
		$size = (($offset+$size)>$maxnum) ? ($maxnum-$offset) : $size;
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetHotWords'), array($maxnum) );
		$memcache = JWMemcache::Instance();
		$hot_words = $memcache->Get( $mc_key );

		$expire = 60 * 15; //15 mins
		if ( false == $hot_words ) {
			//statuses
			$sql = "SELECT idTag, COUNT(1) AS count FROM Status WHERE idTag IS NOT NULL GROUP BY idTag ORDER BY count DESC LIMIT $maxnum";
			$rows = JWDB::GetQueryResult( $sql, true );
			$rows = JWUtility::AssColumn( $rows, 'idTag' );
			
			//followers
			$sql = "SELECT idTag, COUNT(1) AS count FROM TagFollower WHERE idUser IS NOT NULL GROUP BY idUser ORDER BY count DESC LIMIT $maxnum";
			$rows2 = JWDB::GetQueryResult( $sql, true );
			$rows2 = JWUtility::AssColumn( $rows2, 'idTag' );

			$rows = array_merge($rows, $rows2);

			$tag_ids = JWUtility::GetColumn($rows, 'idTag');
			$tags = JWDB_Cache_Tag::GetDbRowsByIds( $tag_ids );

			$hot_words = array();
			foreach( $tags AS $tag_id => $tag ) {
				$hot_words[] = array( 'name' => $tag['name'],
						'count' => $rows[ $tag_id ]['count'],
						);
			}

			$memcache->Set( $mc_key, $hot_words, 0, $expire );
		}
		return array_slice($hot_words, $offset, $size);
	}

	static public function GetBlogItem($size)
	{
		$size = JWDB::CheckInt( $size );
		$user = JWUser::GetUserInfo( 'blog' );

		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetBlogItem'), array($size) );
		$memcache = JWMemcache::Instance();

		$blog_item = $memcache->Get($mc_key);
		if ( false&&$blog_item && $blog_item['time'] >= strtotime($user['timeStamp']) )
		{
			return 	$blog_item['data'];
		}

		$status_ids = JWDB_Cache_Status::GetStatusIdsFromUser($user['id'], $size);
		$status_rows = JWDB_Cache_Status::GetDbRowsByIds( $status_ids['status_ids'] );

		$data = array();
		foreach ( $status_rows AS $id=>$one )
		{
			if ( preg_match("#^(.*)\s+(http://\S+)$#", $one['status'], $matches) )
			{
				array_push( $data, array(
							'desc' => $matches[1],
							'url' => $matches[2],
							));
			}
		}

		$blog_item = array(
				'data' => $data,
				'time' => time(),
				);

		$memcache->Set( $mc_key, $blog_item );

		return $data;
	}

	/**
	 * Cache 15 minutes;
	 */
	static public function GetHotJiWai( $size=3 )
	{
		$size = JWDB::CheckInt( $size );
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetHotJiWai'), array($size) );
		$memcache = JWMemcache::Instance();

		$hot_jiwai = $memcache->Get( $mc_key );

		if ( false==empty($hot_jiwai) ) {
			return $hot_jiwai;
		}

		$expire = 15 * 60;
		$sql = "SELECT s.idThread as id, COUNT(distinct idUser) AS ucount, COUNT(1) AS rcount FROM (Select * FROM Status WHERE timeCreate >(now() - interval 24 hour))s,User u WHERE s.idUser=u.id AND s.idThread IS NOT NULL AND u.protected='N' AND s.idThread IN (SELECT id FROM Status WHERE timeCreate>(now() - interval 24 hour)) GROUP BY s.idThread ORDER BY ucount DESC, rcount DESC LIMIT 0,{$size}";

		$query_rows = JWDB::GetQueryResult($sql, true);
		$visit_thread_ids = JWFunction::GetColArrayFromRows($query_rows, 'id');
		$visit_status_rows = JWDB_Cache_Status::GetDbRowsByIds( $visit_thread_ids );
		$visit_status_rows = JWDB_Cache::SortArrayByKeyOrder( $visit_status_rows, $visit_thread_ids );

		$memcache->Set( $mc_key, $visit_status_rows, 0, $expire );

		return $visit_status_rows;
	}

	/**
	 * 达人标准为 1日内 叽歪数目
	 */
	static public function GetJiWaiDaRenIds($device='gtalk', $size=6)
	{
		if ( false==in_array($device, JWDevice::$daRenArray) )
			return array();

		$maxnum = 100;
		$expire = 60 * 15; // 15 minutes

		$size = JWDB::CheckInt( $size );
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetJiWaiDaRenIds'), array() );
		$memcache = JWMemcache::Instance();
		$jiwai_daren = $memcache->Get( $mc_key );

		if ( empty($jiwai_daren) )
		{
			$yesterday = date('Y-m-d', strtotime('Yesterday') );
			$now = date('Y-m-d H:i:s');
			$in_device = implode( "','", JWDevice::$daRenArray );

			$sql = "SELECT idUser as id, device, COUNT(1) AS count FROM Status FORCE INDEX(IDX__Status__timeCreate) WHERE timeCreate>='$yesterday' AND timeCreate <'$now' and device IN ('$in_device') AND isProtected='N' AND isSignature='N' GROUP BY idUser ORDER BY device,count DESC";
			$row = JWDB::GetQueryResult($sql, true);

			$jiwai_daren = array();
			foreach( JWDevice::$daRenArray AS $one ) {
				$jiwai_daren[ $one ] = array();
			}

			foreach( $row AS $one ) {
				$jiwai_daren[ $one['device'] ][] = $one;
			}

			$memcache->Set($mc_key, $jiwai_daren, 0, $expire);
		}

		$device_daren = $jiwai_daren[ $device ];
		$size = $size < $maxnum ? $size : $maxnum;
		$size = $size < count($device_daren) ? $size : count($device_daren);

		return array_slice( $device_daren, 0, $size );
	}

	static public function GetHeadStatusRows($user_ids) {
		if (is_numeric($user_ids))
			JWDB::CheckInt($user_ids);

		settype($user_ids, 'array');
		if (empty($user_ids)) 
			return array();


		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetHeadStatusRows'), array($user_ids) );
		$memcache = JWMemcache::Instance();
		$status_rows = $memcache->Get( $mc_key );

		if ( false == empty($status_rows)) {
			return $status_rows;
		}

		$user_ids = implode(',', $user_ids);
		$expire = 60 * 5; //5 mins cache

		$sql = "SELECT * FROM (SELECT * FROM Status WHERE idUser IN({$user_ids}) ORDER BY ID DESC)s GROUP BY s.idUser";
		$status_rows = JWDB::GetQueryResult($sql, true);
		$status_rows = JWUtility::AssColumn($status_rows, 'id');

		$memcache->Set( $mc_key, $status_rows, 0, $expire );
		return $status_rows;
	}


	/**
	 * featured mms
	 */
	static public function GetGPicture($size=1) 
	{
		$size = JWDB::CheckInt( $size );
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetPicture'), array($size) );
		$memcache = JWMemcache::Instance();

		$picture_rows = $memcache->Get( $mc_key );

		if ( false==empty($picture_rows) ) {
			return $picture_rows;
		}

		$expire = 5 * 60; // 5 mins
		$sql = "SELECT * FROM Status WHERE statusType='MMS' ORDER BY id DESC LIMIT 0,{$size}";
		$picture_rows = JWDB::GetQueryResult($sql, true);
		$memcache->Set( $mc_key, $picture_rows, 0, $expire );
		return $picture_rows;
	}

	static public function TrendTag($word=null, $size=0) 
	{
		$savenum = self::SIZE_TRENDWORD;
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'TrendTag'), array($savenum) );
		$memcache = JWMemcache::Instance();
		$words = $memcache->Get( $mc_key );

		if ( empty($words) ) {
			$words = array();
		} 

		if ( $word && !is_numeric($word) ) {
			$expire = 24 * 60 * 60; // 1 day
			$word = strtolower($word);
			array_unshift($words, $word);
			$words = array_unique($words);
			if ( count($words) > $savenum ) {
				$words = array_slice($words, 0, $savenum);
			}
			$memcache->Set($mc_key, $words, 0, $expire);
		}

		if ( $size ) {
			return count($words)>$size ? array_slice($words,0,$size) : $words;
		}

		return null;
	}

	static public function TrendWord($word=null, $size=0) 
	{
		$savenum = self::SIZE_TRENDWORD;
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'TrendWord'), array($savenum) );
		$memcache = JWMemcache::Instance();
		$words = $memcache->Get( $mc_key );

		if ( empty($words) ) {
			$words = array();
		} 

		if ( $word && !is_numeric($word) ) {
			$expire = 24 * 60 * 60; // 1 day
			$word = strtolower($word);
			array_unshift($words, $word);
			$words = array_unique($words);
			if ( count($words) > $savenum ) {
				$words = array_slice($words, 0, $savenum);
			}
			$memcache->Set($mc_key, $words, 0, $expire);
		}

		if ( $size ) {
			return count($words)>$size ? array_slice($words,0,$size) : $words;
		}

		return null;
	}

	static public function GetPopkey() {
		$f_tag = array_merge(self::TrendTag(null,3),array('母亲节','小秘密'));
		$f_word = self::TrendWord(null, 5);
		$f_tag = array_unique($f_tag);
		$r = array();
		foreach($f_tag AS $one) $r[] = "[{$one}]"; 
		foreach($f_word AS $one) $r[] = "{$one}"; 
		$r_k = array_rand($r, count($r));
		$r = array_combine($r_k, $r);
		ksort($r);
		return json_encode($r);
	}

	static public function GetSuggestTag($user_id=null) 
	{
		$p_tags = array('笑话','叽歪','小秘密','旅行','工作','学习');
		if ( $user_id == null ) {
			return $p_tags;
		}

		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetSuggestTag'), array($user_id) );
		$memcache = JWMemcache::Instance();
		$tags = $memcache->Get( $mc_key );

		if ( empty($tags) || true) {

			$tag_ids = JWDB_Cache_Status::GetTagIdsTopicByIdUser($user_id);
			$tag_ids = array_keys($tag_ids);
			$tag_rows = JWDB_Cache_Tag::GetDbRowsByIds($tag_ids);
			$tags = JWUtility::GetColumn($tag_rows, 'name');
			$count = count($tags);

			if ( $count == 0 ) {
				$tags = $p_tags;
			}
			else if ( $count < 6 ) {
				$tags = array_slice(array_unique(array_merge($tags,$p_tags)),0,6);
			}
			else {
				$firsts = array_slice($tags, 0, 3);
				$ends = array_slice($tags, $count-3, 3);
				$tags = array_merge($firsts, $ends);
			}

			$memcache->Set($mc_key, $tags, 0, $expire);
		}

		return $tags;
	}

	static public function GetInitJs($onlyurl=true) 
	{
		$jses = array(
				'/lib/mootools/mootools.v1.11.js',
				'/js/onload.js',
				'/js/jiwai.js',
				'/js/buddyIcon.js',
				'/js/location.js',
				'/js/validator.js',
				'/js/seekbox.js',
				'/js/action.js',
				);
		$content = null;
		$timestamp = 0;
		foreach( $jses AS $one ) {
			$absone = JW_ROOT . '/domain/asset/' . $one;
			if(false==file_exists($absone)) break;
			$content[] = ($onlyurl) ? '' : file_get_contents($absone);
			$ftimestamp = filemtime($absone);
			$timestamp = max($timestamp, $ftimestamp);
		}
		if ( $onlyurl ) {
			$timestamp = date('Y-m-d H:i:s', $timestamp);
			return JWTemplate::GetAssetUrl('/jiwai.js', $timestamp);
		}
		return array(
				'time' => $timestamp,
				'content' => join("\n", $content),
				);
	}
}
?>
