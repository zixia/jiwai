<?php
JWTemplate::wml_doctype();
JWTemplate::wml_head();

$status_data =  JWDB_Cache_Status::GetStatusIdsFromUser( $userInfo['id'] , 10);
$status_rows    =  JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids']);

$statuses = array();
foreach( $status_rows as $k=>$s ){
    $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/',"@<a href='/$1/'>$1</a> ", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$render = new JWHtmlRender();
$render->display( 'wo' , array(
                    'userInfo' => $userInfo,
                    'statuses' => $statuses,
                ));

JWTemplate::wml_foot();
?>
