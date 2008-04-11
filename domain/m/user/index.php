<?php
require_once( dirname(__FILE__).'/../config.inc.php' );

$nameOrId = $pathParam = null;
$page = 1;
extract( $_REQUEST, EXTR_IF_EXISTS );

$func = $param = null;
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

$userInfo = JWUser::GetUserInfo( $nameOrId , null, 'nameUrl');
$loginedUserInfo 	= JWUser::GetCurrentUserInfo();

if( empty( $userInfo ) ){
    if( strtolower($nameOrId) === 'public_timeline' ){
        require_once( dirname(__FILE__) . '/public_timeline.inc.php');
        exit(0);
    } elseif( strtolower($nameOrId) === 'download' ){
        require_once( dirname(__FILE__) . '/download.inc.php');
        exit(0);
    }
    redirect_to_404( '/' );
}else{
    $idUser = $userInfo['id'];
    $nameScreen = $userInfo['nameScreen'];
}

$statusTab = null;
switch ( $func ) {
	case 'followings':
		require_once(dirname(__FILE__) . "/followings.inc.php");
		break;
    case 'followers':
		require_once(dirname(__FILE__) . "/followers.inc.php");
		break;

	case 'with_friends':
		$statusTab 	= 'with_friends';
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
