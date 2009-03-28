<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo = JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = abs(intval(@$_GET['page'])) ? abs(intval(@$_GET['page'])) : 1;
$pagesize = 40;
$offset = $pagesize * ($page-1);

$pagination	= new JWPagination(200, $page, $pagesize);

$tags = JWFarrago::GetHotWords($pagesize, $offset);

$shortcut = array( 'public_timeline', 'logout', 'my', 'message' , 'followings', 'index', 'replies');
$pageString = paginate( $pagination, "/t/$tag_row[name]/" );
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
