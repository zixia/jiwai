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

    /**
     * SendMt Raw 
     */

	static public function sendMt( $to, $imageFile, $subject=null, $text=null ) {

		$MMS_HTTP_POST_URL = "http://211.157.106.172:8000/mms/submit";

		$imageContent = @file_get_contents( $imageFile ) ;
		if( empty( $imageContent ) )
			return false;
		
		$smil = null;

		$replace = array( 
				'%APP_ID%' => self::MMS_AID,
				'%GATEWAY_ID%' => self::MMS_GID,
				'%TO%' => $to,
				'%SUBJECT%' => $subject,
				'%PRODUCT_ID%' => self::MMS_PID,
				'%SMIL%' => base64_Encode( $smil ),
				'%TEXT%' => mb_convert_encoding( $text, "GB2312", "UTF-8,GBK"),
				'%BASE64_IMAGE%' => base64_Encode( $imageContent ),
			);

$postData = <<<POSTDATA
<mmsMT>
	<AppID>%APP_ID%</AppID>
	<GatewayID>%GATEWAY_ID%</GatewayID>
	<Receiver>
		<to>%TO%</to>
	</Receiver>
	<Subject>%SUBJECT%</Subject>
	<ProductID>%PRODUCT_ID%</ProductID>
	<MMS>
		<content>
			<smil encode="base64">%SMIL%</smil>
			<item id="1.txt">%TEXT%</item>
			<item id="1.gif" encode="base64">%BASE64_IMAGE%</item>
		</content>
	</MMS>
</mmsMT>
POSTDATA;

		$postContent =  str_replace( array_keys( $replace ), array_values( $replace ), $postData );

		$return = JWNetFunc::DoPost( $MMS_HTTP_POST_URL, $postContent );

		error_log( $return );
		
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
