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
}else{
	$receiverRow = JWUser::GetUserInfo( $value );
	if( empty( $receiverRow )) {
		JWSession::SetInfo('notice', "不存在编号为 {$value} 的用户");
		redirect();
	}
	$value = $receiverRow['nameScreen'];
}

switch($action){
	case 'nudge':
		JWSns::ExecWeb($loginedUserInfo['id'], $action .' '. $value, '挠挠此人');
		break;
	case 'follow':
		JWSns::ExecWeb($loginedUserInfo['id'], $action .' '. $value, '打开关注');
		break;
	case 'leave':
		JWSns::ExecWeb($loginedUserInfo['id'], $action .' '. $value, '取消关注');
		break;
	case 'on':
		JWSns::ExecWeb($loginedUserInfo['id'], $action .' '. $value, '接收通知');
		break;
	case 'off':
		JWSns::ExecWeb($loginedUserInfo['id'], $action .' '. $value, '取消通知');
		break;
	case 'list':
		require_once( './followings.list.php' ); 
		exit(0);
}

redirect();


function leave($idUser, $idFriend){
	JWSns::ExecWeb($idUser, "");
    $userInfo = JWUser::GetUserInfo( $idFriend );
    if ( JWFollower::Destroy($idFriend, $idUser) ) {
        JWSession::SetInfo( 'error', "取消关注退订成功，你将不会再在手机或聊天软件上收到$userInfo[nameScreen]的更新。");
    }else{
        JWSession::SetInfo( 'error', "哎呀！由于系统故障，你未能成功取消关注 $userInfo[nameScreen] …… 请稍后再试吧。");
    }

    redirect();
}

function follow($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );
    if ( JWFollower::Create($idFriend, $idUser) ) {
        JWSession::SetInfo( 'error', "打开通知成功。$userInfo[nameScreen]的更新将会发送到你的手机或聊天软件上。");
    }else{
        JWSession::SetInfo( 'error', "哎呀！由于系统临时故障，你未能成功打开 $userInfo[nameScreen]的通知… 请稍后再试吧。");
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

    if ( JWFollower::IsFollower( $idUser, $idFriend ) ){
        $nudgeMessage = "$loginedUserInfo[nameScreen]挠挠了你一下，提醒你更新JiWai！回复本消息既可更新你的JiWai。";
        if( JWNudge::NudgeToUsers( array($idFriend), $nudgeMessage, 'nudge', 'web' ) ){
            JWSession::SetInfo('error', "我们已经帮你挠挠了$userInfo[nameScreen]一下！期待很快能得到你朋友的回应。");
        }else{
            JWSession::SetInfo('error', "哎呀！由于系统故障，挠挠失败了…… 请稍后再尝试吧。");
        }
    }else{
        JWSession::SetInfo('error', "你现在还不是$userInfo[nameScreen]的好友，不能挠挠。");
    }

    redirect();
    if ( JWFollower::Create($idFriend, $idUser) ) {
        JWSession::SetInfo( 'error', "关注成功。$userInfo[nameScreen]的更新将会发送到你的手机或聊天软件上。");
    }else{
        JWSession::SetInfo( 'error', "哎呀！由于系统临时故障，你未能关注成功$userInfo[nameScreen]… 请稍后再试吧。");
    }

    redirect();
}
function destroy($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );

    $bidirection = false;
    if ( JWUser::IsProtected($idUser) )
        $bidirection = true;

    if ( JWSns::DestroyFriends($idUser, array($idFriend), $bidirection) ) {
        JWSession::SetInfo( 'error', "已经停止对 $userInfo[nameScreen] 的关注了。");
    }else{
        JWSession::SetInfo( 'error', "系统故障，暂时无法删除你关注的人。");
    }

    redirect();
}

function create($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );
	if ( empty($userInfo) ) {
		JWSession::SetInfo('error', '添加关注失败：没有这个用户');
		redirect();
	}

	if ( JWSns::CreateFollower($idFriend, $idUser )) {
		JWSession::SetInfo('error', "已经开始关注 $userInfo[nameScreen]，耶！");
	} else {
		JWSession::SetInfo('error', "哎呀！由于系统故障，添加关注失败了…… 请稍后再尝试吧。。");
	}
    
    redirect();
}
?>
