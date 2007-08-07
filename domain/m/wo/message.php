<?php
require_once( '../config.inc.php' );

$pathParam = null;
$page = 1;
extract( $_REQUEST, EXTR_IF_EXISTS );
$action = $value = null;

JWLogin::MustLogined();

$loginedUserInfo = JWUser::GetCurrentUserInfo();

@list( $action, $value ) = explode( '/', trim( $pathParam, '/' ) );

if( $action == null ) {
    Header('Location: /');
}

switch($action){
    case 'create':
        $shortcut = array( 'index', 'logout' , 'public_timeline', 'my');
        $userInfo = JWUser::GetUserInfo( $value );
        JWRender::Display( 'wo/message_create', array(
                        'userInfo' => $userInfo,
                        'shortcut' => $shortcut,
                        'loginedUserInfo' => $loginedUserInfo,
                    ));
    break;
    case 'send':
        send( $loginedUserInfo['id'], $value );
    break;
    case 'destroy':
        destroy( $loginedUserInfo['id'], $value );
    break;
    case 'inbox':
    case 'sent':
        require_once( './message.inout.php' );
    break;
}

function destroy( $idUser, $value ){
    
    if( false == JWMessage::IsUserOwnMessage( $idUser, $value) ){
        JWSession::SetInfo('error', "您无权删除这条悄悄话（编号 $value）。" );
        redirect();
    }

    $url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '/';
    $redirect = $url;
    $object = 'message';
    $confirm = '确认删除这条悄悄话吗？';

    global $loginedUserInfo;
    $shortcut = array('my','public_timeline','logout','message','index');
    JWRender::Display( 'wo/destroy', array(
                        'object' => $object,
                        'id' => $value,
                        'redirect' => $redirect,
                        'confirm' => $confirm,
                        'shortcut' => $shortcut,
                        'loginedUserInfo' => $loginedUserInfo,
                        ));

}

function send($idUser, $idReceiver){
    $content = isset($_POST['content']) ? $_POST['content'] : null;
    $message = trim( $content );
    $userInfo = JWUser::GetUserInfo( $idReceiver );

    if ( empty($userInfo) || !JWFriend::IsFriend($idReceiver, $idUser) ) {
        JWSession::SetInfo('error', "用户不存在，或用户不是您的好友。");
        redirect();
    }

    if( $message ){
        if ( JWSns::CreateMessage($idUser, $idReceiver, $message ) ){
            JWSession::SetInfo('error', "您的悄悄话已经发送给<a href=\"/$userInfo[nameScreen]/\">$userInfo[nameScreen]</a>了，耶！");
            redirect( '/wo/message/inbox' );
        }else{
            JWSession::SetInfo('error', "哎呀！由于系统临时故障，您的悄悄话未能成功的发送给<a href=\"/$userInfo[nameScreen]/\">$userInfo[nameScreen]</a>，请稍后再试吧。");
        }
        redirect( );
    }
}
?>
