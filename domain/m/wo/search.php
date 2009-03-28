<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo 	= JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = abs(intval(@$_GET['page'])) ? abs(intval(@$_GET['page'])) : 1;

$q = @$_GET['q'];
if ( $q ) {
	$extra = array( 'order_field' => 'time', 'order' => true, );
	$result = JWSearch::SearchStatus($q, $page, 10, $extra);
	$count = $result['count'];
	$pagination = new JWPagination($count, $page, 10);
	$statusRows = JWDB_Cache_Status::GetDbRowsByIds($result['list']);
	$userIds = JWUtility::GetColumn($statusRows, 'idUser');
	$userRows = JWDB_Cache_User::GetDbRowsByIds($userIds);
	$statuses = array();
	foreach( $statusRows as $k=>$s){
		$protected = JWSns::IsProtected( $userRows[$s['idUser']], $loginedIdUser ) || JWSns::IsProtectedStatus( $s, $loginedIdUser );
		if($protected) continue;
		$fs = JWStatus::FormatStatus( $s, false, false, true );
		$s['status'] = $fs['status'];
		$statuses[ $k ] = $s;
	}
	$pageString = paginate( $pagination, '/wo/search/?q='.$q );
}
if ( !strlen($q) ) {
	$tword = JWFarrago::TrendWord(null,1);
	$q = $tword[0];
}

$shortcut = array( 'public_timeline', 'logout', 'my', 'search', 'message' , 'followings', 'index', 'replies' );
JWRender::Display( 'wo/search', array(
			'loginedUserInfo' => $loginedUserInfo,
			'users' => $userRows,
			'statuses' => $statuses,
			'followingsNum' => $followingsNum,
			'followersNum' => $followersNum,
			'shortcut' => $shortcut,
			'pageString' => $pageString,
			));
?>
