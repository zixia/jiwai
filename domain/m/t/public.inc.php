<?php
$page = abs(intval(@$_GET['page'])) ? abs(intval(@$_GET['page'])) : 1;
$pagesize = 40;
$offset = $pagesize * ($page-1);

$pagination	= new JWPagination(200, $page, $pagesize);
$tags = JWFarrago::GetHotWords($pagesize, $offset);
$pageString = paginate( $pagination, "/t/$tag_row[name]/" );

$shortcut = array( 'index', 'public_timeline' );
if( false == empty($loginedUserInfo) ){
    array_push( $shortcut, 'logout','my','favourite', 'search', 'followings','message','replies');
}
JWRender::Display( 't/public', array(
	'loginedUserInfo' => $loginedUserInfo,
    'users' => $userRows,
    'statuses' => $statuses,
    'tags' => $tags,
    'shortcut' => $shortcut,
    'pageString' => $pageString,
	'tag_row' => $tag_row,
));
?>
