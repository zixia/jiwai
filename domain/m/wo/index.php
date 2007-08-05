<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo 	= JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;


$user_status_num= JWDB_Cache_Status::GetStatusNum($loginedIdUser);
$pagination		= new JWPagination($user_status_num, $page);
$status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser($loginedIdUser, 10, $pagination->GetStartPos() );

$status_rows	= JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

$statuses = array();
foreach( $status_rows as $k=>$s){
    $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/',"@<a href='/$1/'>$1</a> ", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$render = new JWHtmlRender();
JWTemplate::wml_doctype();
JWTemplate::wml_head();

$render->display( 'wo/archive', array(
    'userInfo' => $loginedUserInfo,
    'users' => $user_rows,
    'statuses' => $statuses,
));

JWTemplate::wml_foot();
?>
