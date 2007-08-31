<?php
if(!defined('TPL_COMPILED_DIR')) 
    define('TPL_COMPILED_DIR', dirname(__FILE__).'/compiled' );
if(!defined('TPL_TEMPLATE_DIR')) 
    define('TPL_TEMPLATE_DIR', dirname(__FILE__).'/template' );

require_once( dirname(__FILE__).'/../../jiwai.inc.php' );

function buildUrl($url){
    return $url;
    $sessionId = session_id();
    $relayUrl = 'http://m.alpha.jiwai.de' . $url;
    if( strpos( $relayUrl, '?' ) > 0 ) {
        $relayUrl .= "&PHPSESSID=$sessionId";
    } else {
        $relayUrl .= "?PHPSESSID=$sessionId";
    }
    return $relayUrl;
}

function buildReplyUrl($nameScreen){ 
    $url = buildUrl( "/$nameScreen/" );
    return "@<a href=\"$url\">".$nameScreen."</a> ";
}

function paginate($pagination, $url){
    $pageString= '<p>';
    if( $pagination->isShowOlder()){
        $pageString.= '6 <a href="' . JWPagination::BuildPageUrl($url, $pagination->GetOlderPageNo()).'" accesskey="6">下页</a>';
    }
    if( $pagination->isShowOlder() && $pagination->isShowNewer() ){
        $pageString.= '｜';
    }
    if( $pagination->isShowNewer() ){
        $pageString.= '<a href="' . JWPagination::BuildPageUrl($url, $pagination->GetNewerPageNo()).'" accesskey="4">上页</a> 4';
    }
    $pageString.= '</p>';
    return $pageString;
}

function redirect($url = null){
    if ( null == $url ){
        if( isset( $_SERVER['HTTP_REFERER'] ) )
            $url = $_SERVER['HTTP_REFERER'];
        else
            $url = '/';
    }
    Header("Location: $url");
    exit;
}

function friendsop($idUser, $idFriends, $forFollow=false){

    $isArray = is_array( $idFriends ) ;

    settype( $idFriends, 'array' );
    $actions = JWSns::GetUserActions( $idUser, $idFriends );
    $ops = array();

    foreach( $actions as $idFriend=>$one ){
        $actionString = null;
        foreach( $one as $action=>$v ){ 
        
            /*
             * for /wo/followers/
             */
            if( $forFollow == true && $action=='remove' ) {
                $actionString = null;
                break;
            }

            switch( $action ) {
                case 'remove':
                    $actionString .= "<a href=\"/wo/friendship/destroy/$idFriend\">删除</a> | ";
                    break;
                case 'nudge':
                    $actionString .= "<a href=\"/wo/friends/nudge/$idFriend\">挠挠</a> | ";
                    break;
                case 'leave':
                    $actionString .= "<a href=\"/wo/friends/leave/$idFriend\">退订</a> | ";
                    break;
                case 'd':
                    $actionString .= "<a href=\"/wo/message/create/$idFriend\">悄悄话</a> | ";
                    break;
                case 'add':
                    $actionString .= "<a href=\"/wo/friendship/create/$idFriend\">添加</a> | ";
                    break;
                case 'follow':
                    $actionString .= "<a href=\"/wo/friends/follow/$idFriend\">订阅</a> | ";
                    break;
            }
        }
        $actionString = trim( $actionString, ' | ');
        $ops[ $idFriend ] = $actionString;
    }

    return ( $isArray === false ) ? $ops[ $idFriends[0] ] : $ops;
}

function pageTitle(){
    global $pageTitle;
    if( $pageTitle )
        return $pageTitle;
    return "这一刻，你在做什么？"; 
}
?>
