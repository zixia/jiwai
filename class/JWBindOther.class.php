<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de BindOther Class
 */
class JWBindOther {

	const AUTH_TWITTER = 'http://twitter.com/account/verify_credentials.xml';
	const AUTH_FANFOU = 'http://api.fanfou.com/private_messages/inbox.xml';

	const POST_TWITTER = 'http://twitter.com/statuses/update.json';
	const POST_FANFOU = 'http://api.fanfou.com/statuses/update.xml';

	static public function Create( $idUser, $loginName='name', $loginPass='123456', $service='twitter' ) {

		$idUser = JWDB::CheckInt( $idUser );
		$service = strtolower( $service );

		switch( $service ) {
			case 'twitter':
				$flag = self::CheckTwitter( $loginName, $loginPass );
			break;
			case 'fanfou':
				$flag = self::CheckFanfou( $loginName, $loginPass );
			break;
			default:
				$flag = false;
		}

		if( $flag ) {
			$idExist = JWDB::ExistTableRow('BindOther', array( 
				'idUser' => $idUser, 
				'service' => $service,
			));

			$uArray = array(
				'idUser' => $idUser, 
				'service' => $service,
				'loginName' => $loginName,
				'loginPass' => $loginPass,
				'enabled' => 'Y',
			);
			if( $idExist ) {
				JWDB::UpdateTableRow( 'BindOther', $idExist, $uArray );
				return $idExist;
			}else{
				$uArray['timeCreate'] = date('Y-m-d H:i:s');
				return JWDB::SaveTableRow( 'BindOther', $uArray );
			}
		}

		return false;
	}

	static public function Destroy( $idUser, $idBindOrder ) { 
		$idBindOrder = JWDB::CheckInt( $idBindOrder );
		$idUser = JWDB::CheckInt( $idUser );
		$delArray = array(
				'idUser' => $idUser,
				'id' => $idBindOrder,
				);  
		return JWDB::DelTableRow( 'BindOther', $delArray );
	}   

	static public function Disable( $idBindOrder ) {
		$idBindOrder = JWDB::CheckInt( $idBindOrder );
		$uArray = array(
			'enabled' => 'N',
		);
		return JWDB::UpdateTableRow('BindOther', $idBindOrder, $uArray);
	}

	static public function GetBindOther( $idUser ) 
	{
		$idUser = JWDB::CheckInt( $idUser );
		$sql = "SELECT * FROM BindOther WHERE idUser=$idUser";
		$r = JWDB::GetQueryResult( $sql, true );
		if( empty($r) ) 
			return array();

		$rtn = array();
		foreach( $r as $one ) {
			$rtn[ $one['service'] ] = $one;
		}

		return $rtn;
	}

	static public function CheckTwitter( $loginName='name', $loginPass='123456' ) 
	{
		return self::CheckAccount( $loginName, $loginPass, self::AUTH_TWITTER );
	}

	static public function CheckFanfou( $loginName='name', $loginPass='123456' ) 
	{
		return self::CheckAccount( $loginName, $loginPass, self::AUTH_FANFOU );
	}

	static public function CheckAccount( $loginName='name', $loginPass='123456', $checkUrl=self::AUTH_TWITTER ) 
	{
		$authCode = Base64_Encode( "$loginName:$loginPass" );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $checkUrl);  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic $authCode" ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010); 
		curl_exec($ch);
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		return ( $httpCode == 200 );
	}

	static public function PostStatus( $bindOther=array(), $message=null ) 
	{
		if( empty( $bindOther ) || false == isset($bindOther['service']) )
			return true;

		if( $bindOther['enabled'] == 'N' )
			return true;

		$service = $bindOther['service'];
		$loginPass = $bindOther['loginPass'];
		$loginName = $bindOther['loginName'];

		switch( $service ) {
			case 'twitter':
				self::PostTwitter( $loginName, $loginPass, $message );
			break;
			case 'fanfou':
				self::PostFanfou( $loginName, $loginPass, $message );
			break;
		}
		return true;
	}

	static public function PostTwitter( $loginName='name', $loginPass='123456', $message=null ){
		return self::RealPostStatus( $loginName, $loginPass, $message, self::POST_TWITTER );
	}

	static public function PostFanfou( $loginName='name', $loginPass='123456', $message=null ){
		return self::RealPostStatus( $loginName, $loginPass, $message, self::POST_FANFOU );
	}

	static public function RealPostStatus( $loginName='name', $loginPass='123456', $message=null, $postUrl=null ) 
	{
		$authCode = Base64_Encode( "$loginName:$loginPass" );
		$postData = 'status='.urlEncode( $message );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $postUrl);  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic $authCode" ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010); 
		curl_exec($ch);
		curl_close($ch);
	}
}
?>
