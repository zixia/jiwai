<?php
if(!defined('TPL_COMPILED_DIR')) 
    define('TPL_COMPILED_DIR', dirname(__FILE__).'/compiled' );
if(!defined('TPL_TEMPLATE_DIR')) 
    define('TPL_TEMPLATE_DIR', dirname(__FILE__).'/template' );

require_once( dirname(__FILE__).'/../../jiwai.inc.php' );

function buildUrl($url){
    $sessionId = session_id();
    $relayUrl = 'http://m.jiwai.vm' . $url;
    if( strpos( $relayUrl, '?' ) > 0 ) {
        $relayUrl .= "&PHPSESSID=$sessionId";
    } else {
        $relayUrl .= "?PHPSESSID=$sessionId";
    }
    return $relayUrl;
}

function buildReplyUrl($nameScreen){ 
    $url = buildUrl( "/$nameScreen/" );
    return "<a href=\"$url\">".$nameScreen."</a>";
}
?>
