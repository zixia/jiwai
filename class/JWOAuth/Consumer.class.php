<?php
class JWOAuth_Consumer extends OAuthConsumer {
public $callback_url;
public $platform;
public $title;
public $idUser;
public $meta = array();
function __construct() {}
}
function GetOwner() {
	$r = JWOAuth::GetConsumer($this->key);
	return $r['idUser'];
}
?>
