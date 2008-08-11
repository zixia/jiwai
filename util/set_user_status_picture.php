<?php
require_once('../jiwai.inc.php');

/*
$id = 72;

$status_ids = JWStatus::GetNonPictureStatusIdsFromUser( $id );

var_dump( $status_ids );

$picture_ids = JWPicture::GetUserPictureIds( $id, 1 );
var_dump( $picture_ids );

foreach ( $status_ids as $status_id )
{
	JWDB_Cache_Status::SetIdPicture( $status_id, $picture_ids[0] ) ;
}

exit;

*/

$max =  55000;
for($i=0; $i<=$max; $i++)
{
	$u = JWDB_Cache_User::GetDbRowById( $i );
	print $i."\n";
	if ( $u )
	{
		$picture_ids = JWPicture::GetUserPictureIds( $i, 1 );
		if ( !empty($picture_ids) )
		{
			JWSns::SetUserStatusPicture( $i, $picture_ids[0] );
		}
	}
	uSleep(300);
}       
?>
