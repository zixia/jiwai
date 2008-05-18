<?php
class JWOAuth_Server extends OAuthServer {
	public function get_signature_methods() {
		return $this->signature_methods;
	}
	public function get_data_store() {
		return $this->data_store;
	} 
}
?>
