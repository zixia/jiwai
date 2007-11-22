<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

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
