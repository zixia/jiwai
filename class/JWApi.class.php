<?php
/**
 * @package		JiWai.de
 * @copyright		AKA Inc.
 * @author	 	shwdai@gmail.com 
 *
 */
class JWApi{
	/**
	 * Instance of JWApi
	 */
	static private $msInstance;

	const AUTH_HTTP = 1;
	
	/**
	  * Get Authed UserId for API
	  */
	static function GetAuthedUserId(){
		return JWLogin::getLoggedUserId();
	}
	
	/**
	  * Offer an auth method for API
	  * @authType, given Auth Type, defined by self constants
	  *		now only support http auth.
	  */
	static function RenderAuth($authType=self::AUTH_HTTP){
		switch($authType){
			case self::AUTH_HTTP:
				self::RenderAuthHttp();
			break;
		}
		return;
	}
	
	/**
	  * Output HTTP Basic Auth Header for API Authentication
	  */
	static function RenderAuthHttp(){
		header('WWW-Authenticate: Basic realm="JiWai API"');
		header('HTTP/1.0 401 Unauthorized');
		exit;
	}
	
	/**
	  * Rebuild User Array by given user db row.
	  */
	function ReBuildUser(&$user){

		$uInfo = array();

		$uInfo['id'] = $user['id'];
		$uInfo['name'] = $user['nameFull'];
		$uInfo['screen_name'] = $user['nameScreen'];
		$uInfo['description'] = $user['bio'];
		$uInfo['location'] = $user['location'];
		$uInfo['url'] = $user['url'];
		$uInfo['protected'] = $user['protected']=='Y' ? true:false;
		$uInfo['profile_image_url'] = $user['idPicture'];
		
		return $uInfo;
	}

	/**
	  * Rebuild Status Array by given status db row.
	  */
	  
	static function ReBuildStatus(&$status){
		$outInfo = array();
		$outInfo['create_at'] = date("D M d H:i:s O Y",$status['timeCreate']);
		$outInfo['text'] = $status['status'];
		$outInfo['id'] = $status['idStatus'];
		return $outInfo;
	}

	static function ArrayToXml($array, $level=1, $topTagName=''){
		$xml = '';
		if( $topTagName ){
			$xml .= "<$topTagName>\n";
			$level += 1;
		}
		foreach ($array as $key=>$value) {
			$key = strtolower($key);
			if($value===false) 
				$value='false';

			if (is_array($value)) { // 大于一层的 assoc array
				//Add by seek 2007-06-14 4:45
				$subTagName = self::_GetXmlSubTagName($key);
				$xml .= str_repeat("\t",$level)
				."<$key>\n"
				. self::ArrayToXml($value, $level+1, $subTagName)
				. str_repeat("\t",$level)."</$key>\n";
			} else { // 一层的 assoc array
				if (htmlspecialchars($value)!=$value) {
					$xml .= str_repeat("\t",$level)
					."<$key><![CDATA[$value]]></$key>\n";
				} else {
					$xml .= str_repeat("\t",$level).
					"<$key>$value</$key>\n";
				}
			}
		}
		if( $topTagName ){
			$xml .= "</$topTagName>\n";
		}
		return $xml;
	}
	
	/**
	  * Private function, just for build xml.
	  */
	static private function _GetXmlSubTagName($key=null){
		switch($key){
			case 'users':
				return 'user';
			case 'statues':
				return 'status';
			case 'friends':
				return 'friend';
			default:
				return null;
		}
	}
}
?>
