<?php
class JWOAuth_DataStore extends OAuthDataStore {/*{{{*/

	public $idUser;

    function __construct() {/*{{{*/
	$this->idUser = JWLogin::GetCurrentUserId();
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
	$consumer_key = JWDB::EscapeString($consumer->key);
	$token = JWDB::EscapeString($token);
	$sql = "SELECT * FROM OAuthToken WHERE `key` = '$token' AND consumer_key = '$consumer_key' AND type = '$token_type'";
	$arr = JWDB::GetQueryResult($sql);
	if ( ! $arr ) return NULL;
	$atoken = new OAuthToken($arr['key'], $arr['secret']);
        return $atoken;
    }/*}}}*/

    function lookup_nonce($consumer, $token, $nonce, $timestamp) {/*{{{*/
	//return 0;
        return !(JWMemcache::Instance('oauth')->Add('on'.$consumer->key.$timestamp.$nonce, '1', 0, 3600));
    }/*}}}*/

  function new_token($consumer, $type="request") {/*{{{*/
    $key = md5('x'.$type.$consumer->key.rand().time());
    $secret = md5(md5('x'.rand().time()));
    $token = new OAuthToken($key, $secret);
    $sql = "INSERT INTO OAuthToken (consumer_key, `key`, secret, type, idUser) VALUES ('{$consumer->key}', '$key', '$secret', '$type', {$this->idUser})";
    $arr = JWDB::Execute($sql);
    return $token;
  }/*}}}*/

  function new_request_token($consumer) {/*{{{*/
    return $this->new_token($consumer, 'request');
  }/*}}}*/

  function new_access_token($token, $consumer) {/*{{{*/
    $ntoken = $this->new_token($consumer, 'access');
    $sql = "DELETE FROM OAuthToken WHERE consumer_key ='{$consumer->key}' AND `key` = '{$token->key}' AND type = 'request'";
    JWDB::Execute($sql);
    return $ntoken;
  }/*}}}*/
}/*}}}*/
?>
