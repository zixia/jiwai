<?php

require_once JW_ROOT.'lib/OAuth/OAuth.php';

class JWOAuth {
	const MAX_APP_PER_USER = 10;
	static public function &Server(){
		unset($_REQUEST['pathParam']);
		unset($_GET['pathParam']);
		$server = new JWOAuth_Server(new JWOAuth_DataStore());
		$sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$plaintext_method = new OAuthSignatureMethod_PLAINTEXT();
		$server->add_signature_method($sha1_method);
		$server->add_signature_method($plaintext_method);
		return $server;
	}
	static public function CreateConsumer(array $options) {
		$title = $options['title'];
		JWUnicode::unifyName($title);
		if (!$title || $title!=$options['title']) throw new JWException('malformed title');
		$r = JWDB::GetQueryResult('SELECT COUNT(1) AS n FROM OAuthConsumer WHERE idUser = '.$options['idUser']);
		if ($r['n']>=self::MAX_APP_PER_USER) throw new JWException('max number of app exceeded');
		$options['key'] = md5(microtime().mt_rand());
		$options['secret'] = md5(md5(microtime().mt_rand()));
		JWDB::SaveTableRow('OAuthConsumer', $options);
		$r = JWDB::GetQueryResult('SELECT COUNT(1) AS n FROM OAuthConsumer WHERE idUser = '.$options['idUser']);
		if ($r['n']>self::MAX_APP_PER_USER) self::DestroyConsumer($options['idUser'], $options['key']);
		return JWOAuth_DataStore::lookup_consumer($options['key']);
	}
	static public function DestroyConsumer($idUser, $key) {
		return JWDB::DelTableRow('OAuthConsumer', array('idUser'=>$idUser, 'key'=>$key));
	}
	static public function ListConsumer($idUser) {
		return JWDB::GetTableRow('OAuthConsumer', array('idUser'=>$idUser), self::MAX_APP_PER_USER);
	}
	static public function ListToken($idUser) {
		return JWDB::GetTableRow('OAuthToken', array('idUser'=>$idUser, 'type'=>'access'), 100);
	}
	static public function RevokeToken($idUser, $token_key) {
		return JWDB::DelTableRow('OAuthToken', array('idUser'=>$idUser, 'key'=>$token_key));
	}
	static public function AuthorizeToken($idUser, $token_key) {
		$token_key = JWDB::EscapeString($token_key);
		JWDB::Execute("UPDATE OAuthToken SET authorized = true WHERE idUser = $idUser AND `key` = '$token_key'");
	}
    static function GetConsumer($consumer_key) {/*{{{*/
	$consumer_key = JWDB::EscapeString($consumer_key);
	$sql = "SELECT * FROM OAuthConsumer WHERE `key` = '$consumer_key'";
	$arr = JWDB::GetQueryResult($sql);
	if ( ! $arr ) return NULL;
	return $arr;
    }/*}}}*/
    static function GetToken($key) {/*{{{*/
	$key = JWDB::EscapeString($key);
	$sql = "SELECT * FROM OAuthToken WHERE `key` = '$key'";
	$arr = JWDB::GetQueryResult($sql);
	if ( ! $arr ) return NULL;
	return $arr;
    }/*}}}*/
}

?>
