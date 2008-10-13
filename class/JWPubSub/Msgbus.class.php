<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@jiwai.com
 * @date	  	2008/08/25
 */

/**
 * JWPubSub upon Msgbus
 * @ref http://code.google.com/p/msgbus/
 */

if (!defined('MSGBUS_ROOT_PATH'))
define('MSGBUS_ROOT_PATH', 'msgbus');

class JWPubSub_Msgbus extends JWPubSub {
    private $host;
    private $port;
    private $msgbus_root;

    /**
     * Construct with url
     * @param string $url http://10.1.40.10:8888/
     * */
    function __construct($url) {
        $c = parse_url($url);
        if ($c && isset($c['scheme']) && $c['scheme'] == 'http') {
            $this->host = $c['host'];
            $this->port = $c['port'];
            $this->msgbus_root = join('',array(
                        'http://', $this->host, ':', $this->port, '/', MSGBUS_ROOT_PATH
                        ));
        }
    }

    /**
     * Publish data to channel
     * @param string $channel msn, gtalk, etc.
     * @param mixed $data content to publish
     * @see JWPubSub
     * @return boolean
     * */
    function Publish($channel, $data) {
        $publish_path = join('/', array($this->msgbus_root, $channel));
        $publish_content = json_encode($data);
        $ch = curl_init($publish_path);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $publish_content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);

        if (curl_errno($ch)) {
            JWLog::LogFuncName( curl_error($ch) );
            return false;
        }

        return true;
    }

    /**
     * Subscribe to a channel
     * Establish the connection with msgbus daemon with
     * <code>
     *  telnet 10.1.40.10 8888
     *  GET /msgbus/channel HTTP/1.0
     * </code>
     * @param string $channel msn, gtalk, etc.
     * */
    function Subscribe($channel) {
        $get_path = join('/', array('', MSGBUS_ROOT_PATH, $channel));

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false == $socket) {
            die(socket_last_error());
        }

        if (false == socket_connect($socket, $this->host, $this->port)) {
            die(socket_last_error());
        }

        socket_write($socket, "GET $get_path HTTP/1.0\r\n\r\n");
        while ($buf = socket_read($socket, 4096, PHP_NORMAL_READ)) {
            echo $buf;
        }
    }

    /**
     * Unsubscribe
     * @param string $channel msn, gtalk, etc.
     * */
    function Unsubscribe($channel) {
        die("unsubscribe: $channel");
    }

    function PeekMessages() { }
}

?>
