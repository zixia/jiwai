<?php
class JWOAuth_Server extends OAuthServer {
  public function get_signature_methods() {
    return $this->signature_methods;
  }
}
?>
