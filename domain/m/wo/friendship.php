<?php
require_once( '../config.inc.php' );

$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
$action = $value = null;

JWLogin::MustLogined();

$loginedUserInfo = JWUser::GetCurrentUserInfo();

@list( $action, $value ) = explode( '/', trim( $pathParam, '/' ) );

if( $action == null ) {
    Header('Location: /');
}

switch($action){
    case 'destroy':
        destroy($loginedUserInfo['id'], $value);
    break;
    case 'create':
        create($loginedUserInfo['id'], $value);
    break;
}


function destroy($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );

    $bidirection = false;
    if ( JWUser::IsProtected($idUser) )
        $bidirection = true;

    if ( JWSns::DestroyFriends($idUser, array($idFriend), $bidirection) ) {
        JWSession::SetInfo( 'error', "$userInfo[nameScreen] 已经不再是你的好友了。");
    }else{
        JWSession::SetInfo( 'error', "系统故障，暂时无法删除好友。");
    }

    redirect();
}

function create($idUser, $idFriend){
    $userInfo = JWUser::GetUserInfo( $idFriend );
	if ( empty($userInfo) ) {
        JWSession::SetInfo('error', '添加好友失败：没有这个用户');
        redirect();
	}

	if ( JWUser::IsProtected($idFriend) && !JWFriend::IsFriend($idUser, $idFriend) ) {
		if ( JWFriendRequest::IsExist($idUser, $idFriend) ) {
                JWSession::SetInfo('error', "您向$userInfo[nameScreen]发送的添加好友请求，他还没有回应，再等等吧。");
		}else{
            if( JWSns::CreateFriendRequest($idUser, $idFriend) ) {
                JWSession::SetInfo('error', "已经向$userInfo[nameScreen]发送了添加好友请求，希望能很快得到回应。");
            } else {
                JWSession::SetInfo('error', "哎呀！由于系统故障，发送好友请求失败了…… 请稍后再尝试吧。。");
            }
        }
	} else {
		if ( JWSns::CreateFriends($idUser, array($idFriend) )) {
            JWSession::SetInfo('error', "已经将$userInfo[nameScreen]添加为好友，耶！");
		} else {
            JWSession::SetInfo('error', "哎呀！由于系统故障，好友添加失败了…… 请稍后再尝试吧。。");
		} 
	}
    
    redirect();
}
?>
