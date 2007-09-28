<?php
require_once ( dirname(__FILE__).'/config.inc.php' );

function doPost(){
	if( $_POST ) {
		$action = $ids = null;
		extract( $_POST, EXTR_IF_EXISTS );

		if( empty( $ids ) )
			return true;
		
		$delete = ( $action == 'delete' );
		foreach( $ids as $id ) {
			JWQuarantineQueue::FireConference( $id, $action, $delete );
		}

		Header('Location: '.$_SERVER['REQUEST_URI'] );
		exit;
	}
}

doPost();

$queue = JWQuarantineQueue::GetQuarantineQueue( JWQuarantineQueue::T_CONFERENCE ) ;
$users = array();
foreach( $queue as $q ){
	if( false == isset( $users[ $q['idUserFrom'] ] ) )
		$users[ $q['idUserFrom'] ] = JWUser::GetUserInfo( $q['idUserFrom'] );
	if( false == isset( $users[ $q['idUserTo'] ] ) )
		$users[ $q['idUserTo'] ] = JWUser::GetUserInfo( $q['idUserTo'] );
}

JWRender::Display( 'quarantineUserMo' , array(
			'queue'=>$queue,
			'menu_nav' => 'quarantineUserMo',
			'users'=>$users,
		));
?>
