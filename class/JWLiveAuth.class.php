<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	 seek@jiwai.com
 * @version	 $Id$
 */
class JWLiveAuth {

	const AUTH_GOOGLE = 'GOOGLE';
	const AUTH_LIVE = 'LIVE';

	static public $mInstance = array();

	private function __construct(){}

	static public function Instance($service=self::AUTH_LIVE)
	{
		if ( isset(self::$mInstance[$service]) )
			return self::$mInstance[$service];

		switch( $service )
		{
			case self::AUTH_LIVE:
				return self::$mInstance[$service] = new JWLiveAuth_Live();
			case self::AUTH_GOOGLE:
				return self::$mInstance[$service] = new JWLiveAuth_Google();
		}

		return null;
	}

	public function GetConsentUrl() {}
	public function ProcessRequest() {}
	public function GetToken($use_once=false) {}
	public function GetContactList($use_once=false) {}
}

/**
 * Windows Live Delegation Authentication for web Application
 */
class JWLiveAuth_Live extends JWLiveAuth {

	// Comma-delimited list of offers to be used.
	const AUTH_OFFERS = 'Contacts.View';
	const TOKEN_COOKIE = 'Jiwai_de_liveauth_live_token';

	public $mWLL = null;
	/**
	 * construct
	 */
	public function __construct()
	{
		require_once JW_ROOT.'lib/LiveAuth/windowslivelogin.php';
		$setting_xml = CONFIG_ROOT . '/invitation/live.xml';
		$this->mWLL = WindowsLiveLogin::initFromXml($setting_xml);
	}

	public function GetConsentUrl()
	{
		return $this->mWLL->getConsentUrl(self::AUTH_OFFERS);
	}

	public function ProcessRequest()
	{
		$content = null;
		if ( isset($_REQUEST['action']) && 'delauth' == $_REQUEST['action'] ) 
		{
			$consent = $this->mWLL->processConsent($_REQUEST);
			$token = $consent->getToken();
			$_SESSION[ self::TOKEN_COOKIE ] = $token;
		}
		return $consent;
	}

	public function GetToken($use_once=false)
	{
		$consent_token = @$_SESSION[ self::TOKEN_COOKIE ];
		if ( null == $consent_token )
			return null;

		if ( true==$use_once )
		{
			$_SESSION[ self::TOKEN_COOKIE ] = null;
		}

		$token = $this->mWLL->processConsentToken($consent_token);	
		if ( $token && false==$token->IsValid() )
		{
			$token = null;
		}
		return $token;
	}

	public function HexToDec($hex)
	{
		//get signed 64bit integer from javabackend
		return trim( file_get_contents("http://10.1.50.10:8080/hexdec.php?hex=$hex") );
	}

	public function HexToDecU($hex)
	{
		$cmd = '/usr/bin/printf %u 0x'.$hex;
		$p = @popen($cmd, 'r');
		$r = @fgets($p);
		@pclose($p);
		return $r;
	}

	public function GetContactList($use_once=false)
	{
		$token = $this->GetToken($use_once);
		if ( null == $token )
			return array();

		$dat = $token->getDelegationToken();
		
		//intlid is a signed 64bit integer, php wont given it from hexdec;
		$intlid = $this->HexToDec($token->getLocationId());

		$url_contact = "https://livecontacts.services.live.com/users/@C@$intlid/rest/invitationsbyemail";
		$header = array(
			'Authorization: DelegatedToken dt="'. $dat . '"',
		);  

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_contact );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		curl_close($ch);

		if ( is_bool($content) )
			return $content;

		$xml_obj = simplexml_load_string($content);

		$ret = array();
		foreach ( $xml_obj->Contacts->Contact AS $contact )
		{   
			$email = (string)$contact->PreferredEmail;
			$nameScreen = $email;
			if ( $contact->Profiles->Personal->LastName )
			{
				$lastname = (string) $contact->Profiles->Personal->LastName;
				$firstname = (string) $contact->Profiles->Personal->FirstName;
				$nameScreen = $lastname . $firstname ;
			}
			$ret[] = array(
				'nameScreen' => $nameScreen,
				'email' => $email,
			);          
		}     
		return $ret;
	}
}

/**
 * Google AuthSub Authentication for web Application
 */
class JWLiveAuth_Google extends JWLiveAuth {

	// Comma-delimited list of offers to be used.

	const TOKEN_COOKIE = 'Jiwai_de_liveauth_google_token';

	/**
	 * construct
	 */
	public function __construct()
	{
	}

	public function GetConsentUrl()
	{
		$authsub_req = "https://www.google.com/accounts/AuthSubRequest";
		$next = urlEncode( "http://jiwai.de/wo/liveauth/googlebackend" );
		$scope = urlEncode( "http://www.google.com/m8/feeds/" );
		$consent_url = "$authsub_req?scope=$scope&session=1&secure=0&next=$next";
		return $consent_url;
	}

	/**
	 * will store session token
	 */
	public function ProcessRequest()
	{
		$authsub_session = "https://www.google.com/accounts/AuthSubSessionToken";

		$token = null;
		if ( isset($_REQUEST['token']) )
		{
			$token = $_REQUEST['token'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $authsub_session);
			$header = array(
			        'Authorization: AuthSub token="'.$token.'"',
				);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$content = curl_exec($ch);
			curl_close($ch);

			$token = null;
			if ( preg_match('/Token=(\S+)/i', $content, $matches) )
			{
				$token = $matches[1];
				$_SESSION[ self::TOKEN_COOKIE ] = $token;
			}
		}
		return $token;
	}

	public function GetToken($use_once=false)
	{
		$token = @$_SESSION[ self::TOKEN_COOKIE ];

		if ( true==$use_once )
		{
			$_SESSION[ self::TOKEN_COOKIE ] = null;
		}

		return $token;
	}

	public function GetContactList($use_once=false)
	{
		$token = $this->GetToken($use_once);
		if ( null == $token )
			return array();

		$contact_url = "http://www.google.com/m8/feeds/contacts/default/base?max-results=1000&alt=json";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $contact_url);
		$header = array(
		        'Authorization: AuthSub token="'.$token.'"',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		curl_close($ch);

		$response = json_decode( $content, true );
		if ( empty($response) )
			return array();

		$ret = array();
		foreach( $response['feed']['entry'] AS $one )
		{
			$email = $one['gd$email'][0]['address'];
			$title = $one['title']['$t'];
			$ret[] = array(
				'nameScreen' => $title ? $title : $email,
				'email' => $email,
			);
		}
		return $ret;
	}
}
?>
