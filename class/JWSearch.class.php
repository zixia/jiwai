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
	static public function SearchStatus($q, $current_page=1, $page_size=20, $extra=array())
	{
		$q = strtolower( trim($q) );
		$in_user = $in_device = $in_type = $in_tag = null;

		/** advance analyze */
		//in:user
		if ( preg_match("/\s+in\s*:\s*([\S]+)\s*?/", $q, $matches) ){
			$in_user = strtolower($matches[1]);
			$q = preg_replace("/\s+in\s*:\s*([\S]+)/","",$q);
		}
		//device:msn
		if ( preg_match("/\s+device\s*:\s*([\w]+)\s*?/", $q, $matches) ){
			$in_device = strtolower($matches[1]);
			$q = preg_replace("/\s+device\s*:\s*([\w]+)/","",$q);
		}
		//type:mms
		if ( preg_match("/\s+type\s*:\s*([\w]+)\s*?/", $q, $matches) ){
			$in_type = strtolower($matches[1]);
			$q = preg_replace("/\s+type\s*:\s*([\w]+)/","",$q);
		}

		$order = isset($extra['order']) ? (!!$extra['order']) : true;

		$query_info = array(
			'query_string' => $q,
			'order' => $order,
			'current_page' => $current_page,
			'page_size' => $page_size,
		);

		if ( isset($extra['order_field']) ) {
			$query_info['order_field'] = $extra['order_field'];
		}

		/**
		 * deal extra info
		 */
		$in_user .= ' ' . ( isset($extra['in_user']) ? $extra['in_user'] : null );
		$in_device .= ' ' . ( isset($extra['in_device']) ? $extra['in_device'] : null );
		$in_type .= ' ' . ( isset($extra['in_type']) ? $extra['in_type'] : null );
		$in_tag .= ' ' . ( isset($extra['in_tag']) ? $extra['in_tag'] : null );

		/**
		 * consider search syntax
		 */

		$in_user_array = array_unique(preg_split('/[\s,，]+/', strtolower($in_user), -1, PREG_SPLIT_NO_EMPTY));
		$in_device_array = array_unique(preg_split('/[\s,，]+/', strtolower($in_device), -1, PREG_SPLIT_NO_EMPTY));
		$in_type_array = array_unique(preg_split('/[\s,，]+/', strtolower($in_type), -1, PREG_SPLIT_NO_EMPTY));
		$in_tag_array = array_unique(preg_split('/[\s,，]+/', strtolower($in_tag), -1, PREG_SPLIT_NO_EMPTY));

		/**
		 * set query_info
		 */
		if ( false==empty($in_user_array) ) 
			$query_info['user'] = $in_user_array;

		if ( false==empty($in_device_array) ) 
			$query_info['device'] = $in_device_array;

		if ( false==empty($in_type_array) ) 
			$query_info['type'] = $in_type_array;

		if ( false==empty($in_tag_array) ) 
			$query_info['tag'] = $in_tag_array;

		return self::LuceneSearch( self::SEARCH_URL_STATUS, $query_info );
	}

	static private function LuceneSearch($search_url, $query_info=array() )
	{
		/**
		 * format query string
		 */
		$query_string = $query_info['query_string'];
		$query_string = trim($query_string, '+\r\n');
		$query_string = str_replace('-', ' - ', $query_string);
		$query_string = str_replace('+', ' AND ', $query_string);
		$query_string = preg_replace('/\s+/', ' ', $query_string);
		$query_info['query_string'] = $query_string;
		/* end */

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
