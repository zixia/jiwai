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
	static public function GetEScreen($size=3, $offset=0) 
	{
		$esuser_id = 165657;
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

	static public function GetHotWords($size=80) {
		$file = FRAGMENT_ROOT . 'page/hot_words.txt';
		$c = @trim(file_get_contents($file));
		$words = explode(',', $c);
		$styles = array('ads', 'adb', 'ags', 'agb');
		$rand_keys = array_rand($words, count($words));
		$words = array_combine( $rand_keys, $words);
		ksort($words);
		foreach( $words AS $k=>$one ) {
			$words[$k] = array( $one, $styles[rand(0,3)] );
		}
		return array_slice($words, 0, $size);
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
		$sql = "SELECT s.idThread as id, COUNT(1) as count FROM (Select * FROM Status WHERE timeCreate >(now() - interval 24 hour))s WHERE s.idThread IS NOT NULL AND s.idThread IN (SELECT id FROM Status WHERE timeCreate>(now() - interval 24 hour)) GROUP BY s.idThread ORDER BY count DESC LIMIT 0,{$size}";

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

	static public function GetSuggestTag($user_id=null) 
	{
		$suggest_tags = array('圣诞惊喜', '笑话');
		$p_tags = array('叽歪', '读书', '美食', '生活');
		if ( $user_id == null ) {
			return array_merge($suggest_tags, $p_tags);
		}

		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWFarrago', 'GetSuggestTag'), array($user_id) );
		$memcache = JWMemcache::Instance();
		$tags = $memcache->Get( $mc_key );

		if ( empty($tags) ) {
			$expire = 6 * 60 * 60; //6 hours
			$tagids = JWTagFollower::GetFollowingIds($user_id);
			$tagrows = JWDB_Cache_Tag::GetDbRowsByIds($tagids);
			$tags = JWUtility::GetColumn($tagrows, 'name');
			$memcache->Set($mc_key, $tags, 0, $expire);
		}

		$tags = array_merge($tags, $p_tags);
		$need_size = 4;
		for($i=0; $i<$need_size;) {
			if (!isset($tags[$i]))
				break;
			if (in_array($tags[$i], $suggest_tag)) {
				$i++;
				continue;
			}
			$suggest_tags[] = $tags[$i];
			$i++;
		}

		return $suggest_tags;
	}
}
?>
