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
		if( JWLogin::IsLogined() ){
			return intval( $_SESSION['idUser'] );
		}
		if( isset( $_SERVER['PHP_AUTH_USER'] ) ){
			$username_or_email = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];
			return JWUser::GetUserFromPassword( $username_or_email, $password );
		}
		return null;
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
		$uInfo['protected'] = $user['protected']=='Y' ? true : false;
		$uInfo['profile_image_url'] = JWPicture::GetUserIconUrl( $user['id'],'thumb48');
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

	/**
	  * Rebuild Message output, compatiable with twitter
	  */
	static function ReBuildMessage(&$message){
		$mInfo = array();

		$mInfo['id'] = isset($message['id']) ? $message['id'] : $message['idMessage'];
		$mInfo['text'] = $message['message'];
		$mInfo['sender_id'] = $message['idUserSender'];
		$mInfo['recipient_id'] = $message['idUserReceiver'];
		$mInfo['create_at'] = date("D M d H:i:s O Y",$message['timeCreate']);

		$screenNameSenderUser = JWUser::GetUserInfo( $message['idUserSender'], 'nameScreen' );
		$screenNameReceiverUser = JWUser::GetUserInfo( $message['idUserReceiver'], 'nameScreen' );
		$mInfo['sender_screen_name'] = $screenNameSenderUser;
		$mInfo['recipient_screen_name'] = $screenNameReceiverUser;
		
		return $mInfo;
	}

	static function ArrayToXml($array, $level=1, $topTagName=''){
		$xml = '';
		if( $topTagName ){
			$xml .= str_repeat("\t",$level);
			$xml .= "<$topTagName>\n";
			$level += 1;
		}
		foreach ($array as $key=>$value) {
			if( is_numeric($key) ){
				$keySub = self::_GetXmlSubTagName($topTagName);
				$key = $keySub ? $keySub : $key;
			}
			$key = strtolower($key);

			if($value===false) $value='false';

			if (is_array($value)) { // 大于一层的 assoc array
				//Add by seek 2007-06-14 4:45
				$subTagName = self::_GetXmlSubTagName($key);
				if( null != $subTagName ){
					$_subXml = null;
					foreach($value as $sv){
						$_subXml .= self::ArrayToXml($value, $level+1, $subTagName);
					}
				}else{
					$_subXml = self::ArrayToXml($value, $level+1 );
				}
				$xml .= str_repeat("\t",$level)
				."<$key>\n"
				. $_subXml
				. str_repeat("\t",$level)."</$key>\n";
			} else { // 一层的 assoc array
				$value = self::RemoveInvalidChar( $value );
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
			$xml .= str_repeat("\t",$level-1);
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
			case 'direct_messages':
				return 'direct_message';
			default:
				return null;
		}
	}

	/**
	  * Remove Invalid Control Char which will coz XML Breakdown.
	  */
	static public function RemoveInvalidChar($value){
		return $value = preg_replace('/[\x00-\x09\x0b\x0c\x0e-\x19]/U',"",$value);   
	}
}
?>
