<?php
JWTemplate::wml_doctype();
JWTemplate::wml_head();

$status_data =  JWDB_Cache_Status::GetStatusIdsFromUser( $userInfo['id'] , 10);
$status_rows    =  JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids']);

$statuses = array();
foreach( $status_rows as $k=>$s ){
    $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$render = new JWHtmlRender();
$shortcut = array('public_timeline', 'index');
if( JWLogin::isLogined() ) {
    array_push( $shortcut, 'logout' );
}else{
    array_push( $shortcut, 'register' );
}

$render->display( 'wo' , array(
                    'userInfo' => $userInfo,
                    'statuses' => $statuses,
                    'shortcut' => $shortcut,
                ));

JWTemplate::wml_foot();
?>
