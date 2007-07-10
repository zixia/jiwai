<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @datel		2007/07/07
 */

/**
 * JiWai.de Database Interface
 */
interface JWDB_Cache_Interface 
{
	static function OnDirty		( $idPK, $dbRow, $table=null );
}
?>
