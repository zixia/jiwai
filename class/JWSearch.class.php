<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Search Class
 */
class JWSearch {
	/**
	 * Instance of this singleton
	 *
	 * @var JWSearch
	 */
	static private $msInstance = null;

	const SEARCH_URL_USER = 'http://10.1.50.10:8080/user.php';
	const SEARCH_URL_STATUS = 'http://10.1.50.10:8080/status.php';
	const SEARCH_URL_TAG = 'http://10.1.50.10:8080/tag.php';

	const UPDATE_URL_USER = 'http://10.1.50.10:8080/user_update.php';
	const UPDATE_URL_STATUS = 'http://10.1.50.10:8080/status_update.php';
	const UPDATE_URL_TAG = 'http://10.1.50.10:8080/tag_update.php';

	/**
	 * Instance of this singleton class
	 *
	 * @return JWSearch
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Search User
	 */
	static public function SearchUser($q, $current_page=1, $page_size=100)
	{
		$q = strtolower( trim($q) );

		$query_info = array(
			'query_string' => $q,
			'order' => true,
			'current_page' => $current_page,
			'page_size' => $page_size,
		);

		return self::LuceneSearch( self::SEARCH_URL_USER, $query_info );
	}

	/**
	 * Search Tag
	 */
	static public function SearchTag($q, $current_page=1, $page_size=100)
	{
		$q = strtolower( trim($q) );

		$query_info = array(
			'query_string' => $q,
			'order' => true,
			'current_page' => $current_page,
			'page_size' => $page_size,
		);

		return self::LuceneSearch( self::SEARCH_URL_TAG, $query_info );
	}

	/**
	 * Search Status
	 */
	static public function SearchStatus($q, $current_page=1, $page_size=20)
	{
		$q = strtolower( trim($q) );

		/** advance analyze */
		//in:user
		if ( preg_match("/\s+in\s*:\s*([\S]+)\s*?/", $q, $matches) ){
			$in_user = $matches[1];
			$q = preg_replace("/\s+in\s*:\s*([\S]+)/","",$q);
		}
		//device:msn
		if ( preg_match("/\s+device\s*:\s*([\w]+)\s*?/", $q, $matches) ){
			$in_device = $matches[1];
			$q = preg_replace("/\s+device\s*:\s*([\w]+)/","",$q);
		}
		//type:mms
		if ( preg_match("/\s+type\s*:\s*([\w]+)\s*?/", $q, $matches) ){
			$in_type = $matches[1];
			$q = preg_replace("/\s+type\s*:\s*([\w]+)/","",$q);
		}

		$query_info = array(
			'query_string' => $q,
			'order' => true,
			'current_page' => $current_page,
			'page_size' => $page_size,
		);

		if ( isset($in_user) ) 
			$query_info['user'] = $in_user;
		if ( isset($in_device) ) 
			$query_info['device'] = $in_device;
		if ( isset($in_type) ) 
			$query_info['type'] = $in_type;

		return self::LuceneSearch( self::SEARCH_URL_STATUS, $query_info );
	}

	static private function LuceneSearch($search_url, $query_info=array() )
	{
		$encoded_query_info = base64_Encode( json_encode( $query_info ) );
		$post_data = 'q=' . $encoded_query_info;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $search_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = curl_exec($ch);

		$error = json_decode( $return, true );

		if ( $error['error'] )
			return array('count'=>0, 'list'=>array(),);

		return $error;
	}

	/**
	 * update Lucene index
	 * @param bool call , whether call rpc_url directly
	 */
	static public function LuceneUpdate($index='user', $id, $call=true)
	{
		$index = strtolower($index);
		if ( false==in_array($index, array('user','status','tag')) )
			return false;

		if ( false===$call )
		{
			JWPubSub::Instance('spread://localhost/')->Publish('/lucene/update', array(
				'id' => $id,
				'index' => $index,
			));
			return true;
		}

		switch ( $index )
		{
			case 'user':
				$rpc_url = self::UPDATE_URL_USER . '?id=' . $id;
				break;
			case 'status':
				$rpc_url = self::UPDATE_URL_STATUS . '?id=' . $id;
				break;
			case 'tag':
				$rpc_url = self::UPDATE_URL_TAG . '?id=' . $id;
				break;
		}

		if ( false==isset($rpc_url) )
			return false;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $rpc_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = curl_exec($ch);

		$error = json_decode( $return, true );

		if ( $error['error'] )
			return false;

		return true;
	}
}
?>
