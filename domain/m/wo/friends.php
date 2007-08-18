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
    $action = 'list';
}

switch($action){
    case 'leave':
        leave($loginedUserInfo['id'], $value);
    break;
    case 'follow':
        follow($loginedUserInfo['id'], $value);
    break;
    case 'nudge':
        nudge($loginedUserInfo['id'], $value);
    break;
    case 'list':
       require_once( './friends.list.php' ); 
    break;
}


function leave($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );
    if ( JWFollower::Destroy($idFriend, $idUser) ) {
        JWSession::SetInfo( 'error', "退订成功，你将不会再在手机或聊天软件上收到$userInfo[nameScreen]的更新。");
    }else{
        JWSession::SetInfo( 'error', "哎呀！由于系统故障，退订$userInfo[nameScreen]失败了…… 请稍后再试吧。");
    }

    redirect();
}

function follow($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );
    if ( JWFollower::Create($idFriend, $idUser) ) {
        JWSession::SetInfo( 'error', "订阅成功。$userInfo[nameScreen]的更新将会发送到你的手机或聊天软件上。");
    }else{
        JWSession::SetInfo( 'error', "哎呀！由于系统临时故障，你未能成为$userInfo[nameScreen]的粉丝，订阅失败了… 请稍后再试吧。");
    }

    redirect();
}

function nudge($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );
    global $loginedUserInfo;
    if( $userInfo['deviceSendVia'] == 'web' ) {
        JWSession::SetInfo('error', "$userInfo[nameScreen]现在不想被挠挠。。。要不稍后再试吧？");
        redirect();
    }

    if ( JWFriend::IsFriend( $idFriend, $idUser ) ){
        $nudgeMessage = "$loginedUserInfo[nameScreen]挠挠了你一下，提醒你更新JiWai！回复本消息既可更新你的JiWai。";
        if( JWNudge::NudgeUserIds( array($idFriend), $nudgeMessage, 'nudge') ){
            JWSession::SetInfo('error', "我们已经帮你挠挠了$userInfo[nameScreen]一下！期待很快能得到你朋友的回应。");
        }else{
            JWSession::SetInfo('error', "哎呀！由于系统故障，挠挠好友失败了…… 请稍后再尝试吧。");
        }
    }else{
        JWSession::SetInfo('error', "你现在还不是$userInfo[nameScreen]的好友，不能挠挠。");
    }

    redirect();
    if ( JWFollower::Create($idFriend, $idUser) ) {
        JWSession::SetInfo( 'error', "订阅成功。$userInfo[nameScreen]的更新将会发送到你的手机或聊天软件上。");
    }else{
        JWSession::SetInfo( 'error', "哎呀！由于系统临时故障，你未能成为$userInfo[nameScreen]的粉丝，订阅失败了… 请稍后再试吧。");
    }

    redirect();
}
?>
