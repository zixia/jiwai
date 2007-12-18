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

	static public function Create( $user_id, $login_name='name', $login_pass='123456', $service='twitter', $options=array() ) 
	{
		if ( false==isset($options['sync_reply']) )
			$options['sync_reply'] = 'Y';

		if ( false==isset($options['sync_conference']) )
			$options['sync_conference'] = 'Y';

		$user_id = JWDB::CheckInt( $user_id );
		$service = strtolower( $service );

		switch( $service ) 
		{
			case 'twitter':
				$flag = self::CheckTwitter( $login_name, $login_pass );
			break;
			case 'fanfou':
				$flag = self::CheckFanfou( $login_name, $login_pass );
			break;
			default:
				$flag = false;
		}

		if( $flag ) 
		{
			$exist_id = JWDB::ExistTableRow('BindOther', array( 
				'idUser' => $user_id, 
				'service' => $service,
			));

			$up_array = array(
				'idUser' => $user_id, 
				'service' => $service,
				'loginName' => $login_name,
				'loginPass' => $login_pass,
				'enabled' => 'Y',
				'syncReply' => $options['sync_reply'],
				'syncConference' => $options['sync_conference'],
			);
			if( $exist_id ) 
			{
				JWDB::UpdateTableRow( 'BindOther', $exist_id, $up_array );
				return $exist_id;
			}
			else
			{
				$up_array['timeCreate'] = date('Y-m-d H:i:s');
				return JWDB::SaveTableRow( 'BindOther', $up_array );
			}
		}

		return false;
	}

	static public function Destroy( $user_id, $bindother_id ) 
	{ 
		$bindother_id = JWDB::CheckInt( $bindother_id );
		$user_id = JWDB::CheckInt( $user_id );
		$del_array = array(
				'idUser' => $user_id,
				'id' => $bindother_id,
				);  
		return JWDB::DelTableRow( 'BindOther', $del_array );
	}   

	static public function Disable( $bindother_id ) 
	{
		$bindother_id = JWDB::CheckInt( $bindother_id );
		$up_array = array(
			'enabled' => 'N',
		);
		return JWDB::UpdateTableRow('BindOther', $bindother_id, $up_array);
	}

	static public function GetBindOther( $user_id ) 
	{
		$user_id = JWDB::CheckInt( $user_id );
		$sql = "SELECT * FROM BindOther WHERE idUser=$user_id";
		$r = JWDB::GetQueryResult( $sql, true );
		if( empty($r) ) 
			return array();

		$rtn = array();
		foreach( $r as $one ) {
			$rtn[ $one['service'] ] = $one;
		}

		return $rtn;
	}

	static public function CheckTwitter( $login_name='name', $login_pass='123456' ) 
	{
		return self::CheckAccount( $login_name, $login_pass, self::AUTH_TWITTER );
	}

	static public function CheckFanfou( $login_name='name', $login_pass='123456' ) 
	{
		return self::CheckAccount( $login_name, $login_pass, self::AUTH_FANFOU );
	}

	static public function CheckAccount( $login_name='name', $login_pass='123456', $checkUrl=self::AUTH_TWITTER ) 
	{
		$auth_code = Base64_Encode( "$login_name:$login_pass" );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $checkUrl);  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic $auth_code" ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010); 
		curl_exec($ch);
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		return ( $httpCode == 200 );
	}

	static public function PostStatus( $bind_other_row=array(), $message=null ) 
	{
		if( empty( $bind_other_row ) || false == isset($bind_other_row['service']) )
			return true;

		if( $bind_other_row['enabled'] == 'N' )
			return true;

		$service = $bind_other_row['service'];
		$login_pass = $bind_other_row['loginPass'];
		$login_name = $bind_other_row['loginName'];

		switch( $service ) {
			case 'twitter':
				self::PostTwitter( $login_name, $login_pass, $message );
			break;
			case 'fanfou':
				self::PostFanfou( $login_name, $login_pass, $message );
			break;
		}
		return true;
	}

	static public function PostTwitter( $login_name='name', $login_pass='123456', $message=null ){
		$post_data = 'source=jiwai&status='.urlEncode( $message );
		return self::RealPostStatus( $login_name, $login_pass, $post_data, self::POST_TWITTER );
	}

	static public function PostFanfou( $login_name='name', $login_pass='123456', $message=null ){
		$post_data = 'status='.urlEncode( $message );
		return self::RealPostStatus( $login_name, $login_pass, $post_data, self::POST_FANFOU );
	}

	static public function RealPostStatus( $login_name='name', $login_pass='123456', $post_data=null, $post_url=null ) 
	{
		$auth_code = Base64_Encode( "$login_name:$login_pass" );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $post_url);  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic $auth_code" ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010); 
		curl_exec($ch);
		curl_close($ch);
	}
}
?>
