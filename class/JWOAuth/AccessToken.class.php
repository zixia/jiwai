<?php
class JWOAuth_AccessToken extends OAuthToken {
	public $idUser;
	function __construct($key, $secret, $idUser) {
		parent::__construct($key, $secret);
		$this->idUser = $idUser;
	}
}
?>
