<?php
class JWPubSub_Bayeux_Message extends stdClass {
        public function __construct($chan = null) {
                if ($chan) $this->channel = $chan;
        }
        public function __toString() {
                return json_encode($this);
        }
}
?>
