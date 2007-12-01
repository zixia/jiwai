<?php
/**
 * JWPubSub Comet Adaptor Class
 * 
 * @author      FreeWizard
 * @see http://www.cometd.com/
 */
class JWPubSub_Bayeux {
    private $client;
    private function __construct($url) {
        $this->client = new JWPubSub_Bayeux_Client(str_replace('bayeux://', 'http://', $url));
    }

    public function Publish($channel, $data) {
        $this->client->publish($chan, $data);
    }
}
?>
