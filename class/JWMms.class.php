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

	static public function sendMt( $to, $imageFile, $subject=null, $text=null ) {

		$MMS_HTTP_POST_URL = "http://211.157.106.172:8000/mms/submit";

		$imageContent = @file_get_contents( $imageFile ) ;
		if( empty( $imageContent ) )
			return false;
		
		$app_id = 93;
		$gw_id = 1;
		$product_id = 10;
		$smil = null;

		$replace = array( 
				'%APP_ID%' => $app_id,
				'%GATEWAY_ID%' => $gw_id,
				'%TO%' => $to,
				'%SUBJECT%' => $subject,
				'%PRODUCT_ID%' => $product_id,
				'%SMIL%' => base64_Encode( $smil ),
				'%TEXT%' => $text,
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
}
?>

