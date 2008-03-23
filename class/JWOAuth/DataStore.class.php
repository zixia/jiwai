<?php
class JWOAuth_DataStore extends OAuthDataStore {/*{{{*/

	public $idUser;

	function __construct() {/*{{{*/
		$this->idUser = (int)JWLogin::GetCurrentUserId();
	}/*}}}*/

	function &lookup_consumer($consumer_key) {/*{{{*/
		$consumer_key = JWDB::EscapeString($consumer_key);
		$sql = "SELECT * FROM OAuthConsumer WHERE `key` = '$consumer_key'";
		$arr = JWDB::GetQueryResult($sql);
		if ( ! $arr ) return NULL;
		$consumer = new JWOAuth_Consumer();
		foreach ($arr as $k=>$v) {
			if (property_exists($consumer, $k)) $consumer->{$k} = $v;
		}
		return $consumer;
	}/*}}}*/

	function lookup_token($consumer, $token_type, $token) {/*{{{*/
		if ($token_type=='access') {
			$consumer_key = JWDB::EscapeString($consumer->key);
			$token = JWDB::EscapeString($token);
			$sql = "SELECT * FROM OAuthToken WHERE `key` = '$token' AND consumer_key = '$consumer_key'";
			$arr = JWDB::GetQueryResult($sql);
			if ( ! $arr ) return NULL;
			$atoken = new JWOAuth_AccessToken($arr['key'], $arr['secret'], $arr['idUser']);
		} else {
			$atoken = JWMemcache::Instance('oauth')->Get('rt'.$token);
		}
		return $atoken;
	}/*}}}*/

	function lookup_nonce($consumer, $token, $nonce, $timestamp) {/*{{{*/
		//return 0;
		return !(JWMemcache::Instance('oauth')->Add('on'.$consumer->key.$timestamp.$nonce, '1', 0, 3600));
	}/*}}}*/

	function new_request_token($consumer) {/*{{{*/
		$key = md5('rt'.$consumer->key.rand().time());
		$secret = md5(md5('x'.rand().time()));
		$token = new JWOAuth_RequestToken($key, $secret, $consumer->key);
		JWMemcache::Instance('oauth')->Set('rt'.$key, $token, 0, 86400);
		return $token;
	}/*}}}*/

	function new_access_token($token, $consumer) {/*{{{*/
		$key = md5('at'.$consumer->key.rand().time());
		$secret = md5(md5('x'.rand().time()));
		if (!$token->idUser) {
			throw new OAuthException('Unauthorized Token');
		}
		try{
			JWDB::SaveTableRow('OAuthToken', array(
				'consumer_key' => $consumer->key,
				'key' => $key,
				'secret' => $secret,
				'idUser' => $token->idUser
			));
			JWMemcache::Instance('oauth')->Del('rt'.$token->key);
			$ntoken = new OAuthToken($key, $secret);
		} catch (Exception $e) {
			$consumer_key = JWDB::EscapeString($consumer->key);
			$sql = "SELECT * FROM OAuthToken WHERE `idUser` = '{$token->idUser}' AND consumer_key = '$consumer_key'";
			$arr = JWDB::GetQueryResult($sql);
			if ( ! $arr ) return NULL;
			$ntoken = new OAuthToken($arr['key'], $arr['secret']);
		}
		return $ntoken;
	}/*}}}*/
}/*}}}*/
?>
