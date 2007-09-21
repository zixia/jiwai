<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de nce Class
 */
class JWIMOnline {
	/**
	 * Instance of this singleton
	 *
	 * @var JWIMOnline
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWIMOnline
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Get IMOnline ById
	 */
	static public function GetDbRowById($idIMOnline){
		$idIMOnline = JWDB::CheckInt( $idIMOnline );
		$sql = <<<_SQL_
SELECT * FROM IMOnline
	WHERE id = $idIMOnline
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}

	/**
	 * Get ServerAddress from Type
	 */
	static public function GetServerAddressByType($type='msn'){
		$sql = <<<_SQL_
SELECT serverAddress FROM IMOnline 
	WHERE type='$type' 
	GROUP BY serverAddress 
	ORDER BY COUNT(1) ASC
	limit 1
_SQL_;
		$row = JWDB::GetQueryResult( $sql, false );
		if( empty( $row ) )
			return null;
		return $row['serverAddress'];
	}

	/**
	 * Get IMOnline By Short cut
	 */
	static public function GetDbRowByAddressType($address, $type){
		$shortcut = "$type:$address";
		return self::GetDbRowByShortcut( $shortcut );
	}

	/**
	 * Get IMOnline By Short cut
	 */
	static public function GetDbRowsByAddressTypes( $addressTypeArray ){
		if( false === is_array( $addressTypeArray ) )
			return array();
		
		$shortcutArray = array();
		foreach( $addressTypeArray as $addressType){
			array_push( $shortcutArray, "$addressType[type]:$addressType[address]" );
		}
		if( empty( $shortcutArray ) )
			return array();

		$shortcutString = implode( "','", $shortcutArray );
		
		$sql = <<<_SQL_
SELECT type, address,shortcut, onlineStatus, timeUpdate
	FROM IMOnline
	WHERE shortcut IN ('$shortcutString')
_SQL_;

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) ){
			return array();
		}
		
		$ret = array();
		foreach( $rows as $row ){
			$ret[ $row['shortcut'] ] = $row;
		}

		return $ret;
	}

	/**
	 * Get IMOnline By Shortcut
	 */
	static public function GetDbRowByShortcut( $shortcut ){

		$sql = <<<_SQL_
SELECT * FROM IMOnline
	WHERE shortcut = '$shortcut'
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );
		return $row;
	}



	/**
	 * Create IMOnline Setting
	 */
	static public function Create( $address, $type='msn', $serverAddress=null, $onlineStatus = 'ONLINE' ){

		$onlineStatusString = self::GetStatusString( $onlineStatus );

		return JWDB::SaveTableRow('IMOnline', array(
					'shortcut' => "$type:$address",
					'type' => $type,
					'address' => $address,
					'serverAddress' => $serverAddress,
					'onlineStatus' => $onlineStatusString,
					));
	}

	/**
	 * Update IMOnline Setting
	 */
	static public function Update( $idIMOnline, $serverAddress = null, $onlineStatus = 'ONLINE' ){
		$idIMOnline = JWDB::CheckInt( $idIMOnline );
		
		$onlineStatusString = self::GetStatusString( $onlineStatus );

		return JWDB::UpdateTableRow( 'IMOnline' , $idIMOnline, array(
						'onlineStatus' => $onlineStatusString,
						'serverAddress' => $serverAddress,
					));
	}

	/**
	 * Set IMOnline Status
	 */
	static public function SetIMOnline( $address, $type='msn', $serverAddress = null, $onlineStatus = 'ONLINE' ){

		$iMOnlineRow = self::GetDbRowByAddressType( $address, $type );

		if( empty( $iMOnlineRow ) ){
			return self::Create( $address, $type, $serverAddress, $onlineStatus );
		}

		$idIMOnline = $iMOnlineRow['id'] ;
		return self::Update( $idIMOnline, $serverAddress, $onlineStatus );
	}

	/**
	 * Get DB Status String
	 */
	static public function GetStatusString( $status='Y' ) {
		$status = strtoupper( $status ) ;
		switch( $status ){
			case 'Y':
			case 'ON':
			case 'ONLINE':
			case 'AVAILABLE':
				return 'ONLINE';
			case 'N':
			case 'OFF':
			case 'OFFLINE':
			case 'FLN':
			case 'ERROR':
			case 'UNAVAILABLE':
				return 'OFFLINE';
			case 'A':
			case 'AWY':
			case 'AWAY':
				return 'AWAY';
		}
		return 'ONLINE';
	}
}
?>
