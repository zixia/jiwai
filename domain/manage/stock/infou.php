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

$limit = 20;
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;


$idUserStocks = JWComStockAccount::GetIdUsersByType(JWComStockAccount::T_STOCK);
$idUserCates = JWComStockAccount::GetIdUsersByType(JWComStockAccount::T_CATE);
$idUsers = array_merge( $idUserCates, $idUserStocks );

$queueNum = JWQuarantineQueue::GetQuarantineQueueNum( JWQuarantineQueue::T_CONFERENCE, $idUsers ) ;
$pagination = new JWPagination($queueNum, $page, $limit);

$queue = JWQuarantineQueue::GetQuarantineQueue( JWQuarantineQueue::T_CONFERENCE, $idUsers ,
						$pagination->GetStartPos(), $limit ) ;
$users = array();
foreach( $queue as $q ){
	if( false == isset( $users[ $q['idUserFrom'] ] ) )
		$users[ $q['idUserFrom'] ] = JWUser::GetUserInfo( $q['idUserFrom'] );
	if( false == isset( $users[ $q['idUserTo'] ] ) )
		$users[ $q['idUserTo'] ] = JWUser::GetUserInfo( $q['idUserTo'] );
}

JWRender::Display( 'infou' , array(
			'queue'=>$queue,
			'menu_nav' => 'infou',
			'users'=>$users,
	 		'pagination' => $pagination,
			'page' => $page,
			'queueNum' => $queueNum,
		));
?>
