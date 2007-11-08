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

	if ( JWUser::IsProtected($idFriend) && !JWFriend::IsFriend($idUser, $idFriend) ) {
		if ( JWFriendRequest::IsExist($idUser, $idFriend) ) {
                JWSession::SetInfo('error', "你向$userInfo[nameScreen]发送的关注请求，他还没有回应，再等等吧。");
		}else{
            if( JWSns::CreateFriendRequest($idUser, $idFriend) ) {
                JWSession::SetInfo('error', "已经向$userInfo[nameScreen]发送了关注请求，希望能很快得到回应。");
            } else {
                JWSession::SetInfo('error', "哎呀！由于系统故障，添加关注请求失败了…… 请稍后再尝试吧。。");
            }
        }
	} else {
		if ( JWSns::CreateFriends($idUser, array($idFriend) )) {
            JWSession::SetInfo('error', "已经开始关注 $userInfo[nameScreen]，耶！");
		} else {
            JWSession::SetInfo('error', "哎呀！由于系统故障，添加关注失败了…… 请稍后再尝试吧。。");
		} 
	}
    
    redirect();
}
?>
