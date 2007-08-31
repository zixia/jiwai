<?php
require_once( '../config.inc.php' );

$pathParam = $status = null;
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
        destroy( $loginedUserInfo['id'], $value );
    break;
    case 'update':
        update( $loginedUserInfo['id'], trim($status) );
    break;
}

function destroy( $idUser, $value ){
    
    if( false == JWStatus::IsUserOwnStatus( $idUser, $value) ){
        JWSession::SetInfo('error', "你无权删除这条更新（编号 $value）。" );
        redirect();
    }

    $url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '/';
    $redirect = $url;
    $object = 'status';
    $confirm = '确认删除这条更新吗？';

    global $loginedUserInfo;
    $shortcut = array('my','public_timeline','logout','message','index', 'friends');
    JWRender::Display( 'wo/destroy', array(
                        'object' => $object,
                        'id' => $value,
                        'redirect' => $redirect,
                        'confirm' => $confirm,
                        'shortcut' => $shortcut,
                        'loginedUserInfo' => $loginedUserInfo,
                        ));

}

function update($idUser, $status) {
    if( $status ){ 

        /*
         *	为了 /help/ 留言板的更新都自动加上 @help
         */
        $isHelp = false;
        $helpUserId	= JWUser::GetUserInfo('help', 'idUser');
        if ( false !== strpos( $_SERVER['HTTP_REFERER'], 'jiwai.de/help/' )
                && $idUser != $helpUserId
                && !preg_match('/^@help /',$status) ) {
                $status = '@help ' . $status;
                $isHelp = true;
        }

        $robotMsg = new JWRobotMsg();
        $robotMsg->Set( $idUser , 'web', $status, 'web' );
        $replyMsg = JWRobotLogic::ProcessMo( $robotMsg );

        if( $replyMsg === false ) {
            JWLog::Instance()->Log(LOG_ERR, "Create($idUser, $status) failed");
        }

        if( false == empty( $replyMsg ) ){
            JWSession::SetInfo('error', $replyMsg->GetBody() );
        }else{
            if( $isHelp ) {
                JWSession::SetInfo('error', "你给叽歪de留言成功！");
            }else{
                JWSession::SetInfo('error', "叽歪成功！");
            }
        }
    }
    header('Location: /wo/' );
}
?>
