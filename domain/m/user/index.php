<?php
require_once( dirname(__FILE__).'/../config.inc.php' );

$nameOrId = $pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

$func = $param = null;
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

$userInfo = JWUser::GetUserInfo( $nameOrId );
if( empty( $userInfo ) ){
    if( strtolower($nameOrId) === 'public_timeline' ){
        require_once( dirname(__FILE__) . '/public_timeline.inc.php');
        exit(0);
    }
    echo "no user";
    exit(0);
}else{
    $idUser = $userInfo['id'];
    $nameScreen = $userInfo['nameScreen'];
}

switch ( $func ) {
	case 'picture':
		require_once(dirname(__FILE__) . "/picture.inc.php");

		// get rid of file ext and dot: we know what type it is.
		//$param = preg_replace('/\.[^.]*$/','',$param);
		list($pict_size) = split('[\.\/]',$param);

		// TODO: http://jiwai.de/zixia/picture/thumb48/para.jpg
		user_picture($page_user_id, $pict_size);
		break;

	case 'statuses':
		require_once(dirname(__FILE__) . "/statuses.inc.php");

		if ( preg_match('/^(\d+)$/',$param,$matches) ) {
			$status_id = intval($matches[1]);
			user_status($page_user_id, $status_id);
		} else {
			JWTemplate::RedirectTo404NotFound();
			exit(0);
		}
		break;

	case 'friends':
		// 用户好友列表
		require_once(dirname(__FILE__) . "/friends.inc.php");
		user_friends($page_user_id);	
		break;

	case 'favourites':
		require_once(dirname(__FILE__) . "/favourites.inc.php");
		user_favourites($page_user_id);	
		break;

	case 'with_friends':
		$g_user_with_friends 	= true;

		// fall to default
	default:
		if ( 'help'===strtolower($nameScreen) )
		{
			require_once(dirname(__FILE__) . '/help.inc.php');
			exit(0);
		}
		require_once(dirname(__FILE__) . "/wo.inc.php");
		break;
}
exit(0);
?>
