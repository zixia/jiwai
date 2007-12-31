<?php
require_once( '../config.inc.php' );

$pathParam = $redirect = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
$action = $value = null;

JWLogin::MustLogined();

$loginedUserInfo = JWUser::GetCurrentUserInfo();

@list( $action, $value ) = explode( '/', trim( $pathParam, '/' ) );

if( $action == null ) {
	Header('Location: /');
}

switch($action){
	case 'message':
		$messageRow = JWMessage::GetDbRowById( $value );
		if( empty($messageRow) ||
			false == ( $messageRow['idUserSender'] == $loginedUserInfo['id']
				|| $messageRow['idUserReceiver'] == $loginedUserInfo['id']
			)
		){
			JWSession::SetInfo('error', "你无权删除这条悄悄话（编号 $value）。");
			redirect( $redirect );
		}else{

			$flag = true;
			if( $flag && $messageRow['idUserSender'] == $loginedUserInfo['id'] ) {
				$flag &= JWMessage::SetMessageStatus($value, JWMessage::OUTBOX, JWMessage::MESSAGE_DELETE);
			}
			if( $flag && $messageRow['idUserReceiver'] == $loginedUserInfo['id'] ) {
				$flag &= JWMessage::SetMessageStatus($value, JWMessage::INBOX, JWMessage::MESSAGE_DELETE);
			}

			if( $flag ) {
				JWSession::SetInfo('error', "悄悄话已经被删除啦！");
			}else{
				JWSession::SetInfo('error', "哎呀！由于系统故障，删除悄悄话失败了…… 请稍后再试。。");
			}
		}
		redirect( $redirect );
	break;
	case 'status':
		if( JWStatus::IsUserOwnStatus( $loginedUserInfo['id'], $value ) ){
			if( JWStatus::Destroy( $value ) ){
				JWSession::SetInfo('error', "更新已经被删除啦！");
			}else{
				JWSession::SetInfo('error', "哎呀！由于系统故障，删除更新失败了…… 请稍后再试。。");
			}
		}else{
			JWSession::SetInfo('error', "你无权删除这条更新（编号 $value）。");
		}
		redirect( $redirect );
	break;
	case 'favourite':
		if( JWFavourite::IsFavourite( $loginedUserInfo['id'], $value ) ){
			if( JWFavourite::Destroy( $loginedUserInfo['id'], $value ) ){
				JWSession::SetInfo('error', "收藏已经被取消啦！");
			}else{
				JWSession::SetInfo('error', "哎呀！由于系统故障，取消收藏失败了…… 请稍后再试。。");
			}
		}else{
			JWSession::SetInfo('error', "你没有收藏过这条更新（编号 $value）。");
		}
		redirect( $redirect );
	break;
}
?>
