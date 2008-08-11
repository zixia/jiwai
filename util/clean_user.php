<?php
require_once('../jiwai.inc.php');

$max =  67594;

for($i=0; $i<=$max; $i++)
{
	$u = JWDB_Cache_User::GetDbRowById( $i );
	print $i."\n";
	if ( $u )
	{
		JWDB_Cache_User::OnDirty($u);
		uSleep(300);
	}
}       
?>
