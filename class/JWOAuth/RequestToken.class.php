<?php
class JWOAuth_RequestToken extends OAuthToken {
	public $authorized = false;
	public $idUser = 0;
	public $consumer_key;
	function __construct($key, $secret, $consumer_key) {
		parent::__construct($key, $secret);
		$this->consumer_key = $consumer_key;
	}
}
?>
