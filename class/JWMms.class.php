<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	shwdai@gmail.com
 */

/**
 * JiWai.de Mms Class
 */
class JWMms {

	const MMS_AID = 12;   //App_id
	const MMS_GID = 1;    //gateway_id
	const MMS_PID = 0;    //product_id

	/**
	 * Instance of this singleton
	 *
	 * @var JWSms
	 */
	static $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWSms
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}

	function __construct()
	{
	}

	static public function sendStatusMMSMt( $mobileNo, $mmsId, $thumb='picture') {

		$mobileRow = JWMobile::GetDbRowByMobileNo( $mobileNo );
		if( $mobileRow['supplier'] != 'MOBILE' ) {
			return true;
		}

		$statusRow = JWStatus::GetDbRowById( $mmsId );

		if( empty($statusRow) || empty($statusRow['idPicture']) || $statusRow['isMms']=='N' )
			return true;

		$mmsRow = JWPicture::GetDbRowById( $statusRow['idPicture'] );
		if( false == empty($mmsRow) || $mmsRow['class'] == 'MMS' ){
			$pictureFile = JWPicture::GetFullPathNameById( $mmsRow['id'] , $thumb);
			if( file_exists( $pictureFile ) ) {
				return self::SendMt( $mobileNo, $pictureFile, $statusRow['status'], $statusRow['status'] );
			}

			$pictureFile = JWPicture::GetUrlById( $mmsRow['id'] );
			return self::SendMt( $mobileNo, $pictureFile, $statusRow['status'], $statusRow['status'] );
		}

		return true;
	}

	/**
	* SendMt Raw 
	*/
	static public function SendMt( $mobileNo, $imageFile, $subject=null, $text=null ) {

		$MMS_HTTP_POST_URL = "http://211.157.106.172:8000/mms/submit";

		$imageContent = @file_get_contents( $imageFile ) ;
		if( empty( $imageContent ) )
			return false;

		$mimeType = mime_content_type( $imageFile );
		$suffix = 'jpg';
		if( $mimeType ) {
			@list( $_, $suffix ) = explode( '/', $mimeType );
		}else{
			if( preg_match( '/\.(\w+)$/', basename($imageFile), $matches) ) {
				$suffix = $matches[1];
			}
		}
		
		$smil = null;
		$appId = self::MMS_AID;
		$gateId =  self::MMS_GID;
		$to = $mobileNo;
		$subject = $subject = mb_convert_encoding( $subject, "GB2312", "UTF-8,GB2312");
		$productId = self::MMS_PID;
		$smil = base64_Encode( $smil );
		$text = mb_convert_encoding( $text, "GB2312", "UTF-8,GB2312");
		$imageContent = base64_Encode( $imageContent );

$postData = <<<POSTDATA
<mmsMT>
	<AppID>$appId</AppID>
	<GatewayID>$gateId</GatewayID>
	<Receiver>
		<to>$to</to>
	</Receiver>
	<Subject>$subject</Subject>
	<ProductID>$productId</ProductID>
	<MMS>
		<content>
			<smil encode="base64">$smil</smil>
			<item id="1.txt" encode="base64">$text</item>
			<item id="1.$suffix" encode="base64">$imageContent</item>
		</content>
	</MMS>
</mmsMT>
POSTDATA;

		$return = JWNetFunc::DoPost( $MMS_HTTP_POST_URL, $postData );

		return ( $return ) ? true : false;
	}

	/*
	 *
	 * @return true is succeed, false is err
	 */
	static public function ReceiveMo( $postedXml , $nameVar='id', $encodeVar='encode' )
	{
		$mmsObject = simplexml_load_string( $postedXml );

		if( false == $mmsObject ) {
		    JWLog::Instance()->Log(LOG_INFO, "Parse postedXml failed." );
		    return false;
		}

		$sender = (string) @$mmsObject->Sender;
		$messageId = uniqid( 'CMO' );
		$directoryName = "${messageId}-${sender}";

		$waitingDir = JWMmsLogic::GetWaitingDir() .'/' . $directoryName;
		$unDealDir = JWMmsLogic::GetUnDealDir() .'/' . $directoryName;
		$ret = @mkdir( $waitingDir , 0777, true );

		if( false == $ret ) {
		    JWLog::Instance()->Log(LOG_INFO, "Create Directory $waitingDir failed." );
		    return false;
		}

		$ret = true;
		$contentObject = $mmsObject->MMS->content;

		for( $index=0; $item = $contentObject->item[$index]; $index++ ){

		    $attr = array(
			$nameVar => null,
			$encodeVar => null,
		    );

		    foreach( $item->attributes() as $k=>$n ) {
			$attr[ $k ] = $n;
		    }

		    
		    $contentValue = (string) $item;
		    if( strtoupper( $attr[ $encodeVar ] ) == 'BASE64' ) {
			$contentValue = base64_decode( $contentValue );
		    }
		    

		    $tempFile = tempnam('/tmp', 'MOMMS');
		    $fp = @fopen( $tempFile, 'w+' );
		    $ret = ( $fp 
			&& fwrite( $fp, $contentValue )
			&& fclose( $fp ) 
			);


		    if( $ret == false ){
			JWLog::Instance()->Log(LOG_INFO, "Create Tempfile failed." );
			return false;
		    }
		    
		    if( $attr[ $nameVar ] == null ) {
			$id = "part-$index";
			$typeinfo = @mime_content_type( $tempFile );
			$type = $suffix = null;
			@list($type, $suffix) = explode('/', $typeinfo );
			$id .= '.' . $suffix;
		    }

		    $realFile = $waitingDir . '/' . $attr[ $nameVar ];

		    $ret = $ret && copy( $tempFile, $realFile ) && unlink( $tempFile );
		}

		return $ret && rename( $waitingDir, $unDealDir );
	}
}
?>
