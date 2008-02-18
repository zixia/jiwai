<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  wqsemc@jiwai.com
 * @version	 $Id$
 */

/**
 * 
 */

class JWUrlMap 
{
	/**
	 * Instance of this singleton
	 *
	 * @var 
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return 
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}

	static public function Create( $srcUrl=null, $destUrl=null, $metaInfo=array(), $options=array() )
	{
		$type = isset( $options['type'] ) ? $options['type'] : 'photo';
		$idPartner = isset( $options['idPartner'] ) ? $options['idPartner'] : null;

		return JWDB::SaveTableRow( 'UrlMap', array(
			'srcUrl' => $srcUrl,
			'destUrl' => $destUrl,
			'metaInfo' => self::EncodeBase64Serialize( $metaInfo ),
			'type' => $type,
			'idPartner' => $idPartner,
		));
	}

	static public function GetDbRowByDescUrl( $url )
	{
		$condition = array(
			'destUrl' => $url,
		);
		$row = JWDB::GetTableRow( 'UrlMap', $condition, 1 );

		if ( true==empty($row) )
			return array();
		$row['metaInfo'] = self::DecodeBase64Serialize( $row['metaInfo'] );

		return $row;
	}

	/**
	 * Encode metaInfo
	 */
	static private function EncodeBase64Serialize( $metaInfo = array()){
		return Base64_Encode( serialize( $metaInfo ) );
	}

	/**
	 * Decode metaInfo 
	 */
	static private function DecodeBase64Serialize( $metaString ) {
		return @unserialize( Base64_Decode( $metaString ) );
	}
}
