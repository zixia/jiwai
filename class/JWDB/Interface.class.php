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
interface JWDB_Interface 
{
	static function SaveTableRow	( $table, $condition );
	static function DelTableRow		( $table, $condition );
	static function ExistTableRow	( $table, $condition );
	static function UpdateTableRow	( $table, $idPK, $condition);
	static function GetTableRow		( $table, $condition, $limit=1 );

	static function GetQueryResult	( $sql, $moreThanOne=false, $forceReload=false);
	static function GetMaxId		( $table, $condition);
}
?>
