<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

if ( ($idUser=JWLogin::GetCurrentUserId())
		&& array_key_exists('_method',$_REQUEST)
		&& array_key_exists('pathParam',$_REQUEST) )
{

	$method = $_REQUEST['_method'];
	$param = $_REQUEST['pathParam'];

	$idStatus = null;

	if ( preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$idStatus = $match[1];

		if ( $method==='delete' )
		{
			if ( JWStatus::IsUserCanDelStatus($idUser, $idStatus))
			{
				$statusRow = JWStatus::GetDbRowById($idStatus);
				JWStatus::Destroy($idStatus);
				
				if (defined('BETA')) {
if (!extension_loaded('spread')) dl('spread.so'); //FIXME to be removed
				JWPubSub::Instance('spread://localhost/')->Publish('/statuses/destroy', array('idUser'=>$idUser, 'idStatus'=>$idStatus)); //FIXME to be moved to core class like JWStatus
				} else
				if (JWFacebook::Verified($idUser)) JWFacebook::RefreshRef($idUser);
				if (false == empty($statusRow) && $statusRow['idThread'] )
				{
					echo JWDB_Cache_Status::GetCountReply( $statusRow['idThread'] );
				}
				return true;
			}
			else
			{
				JWSession::SetInfo( 'error',"你无权删除这条更新（编号 $idStatus ）" );
			}
		}
	}
}

JWTemplate::RedirectBackToLastUrl('/');
?>
