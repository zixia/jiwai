<?php
require_once JW_ROOT.'lib/LiveAuth/windowslivelogin.php';

/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	 seek@jiwai.com
 * @version	 $Id$
 * this class use windows live delegation authentication mechanism;
 */
class JWLiveAuth {
	
	// Comma-delimited list of offers to be used.
	const AUTH_OFFERS = 'Contacts.View';

	const TOKEN_COOKIE = 'Jiwai_de_delauth_token';

	static public $mWLL = null;

	static private function Init()
	{
		if ( null == self::$mWLL )
		{
			$setting_xml = CONFIG_ROOT . '/invitation/live.xml';
			self::$mWLL = WindowsLiveLogin::initFromXml($setting_xml);
		}
	}

	static public function GetConsentUrl()
	{
		self::Init();
		return self::$mWLL->getConsentUrl(self::AUTH_OFFERS);
	}

	static public function ProcessRequest()
	{
		self::Init();
		$content = null;
		setcookie( self::TOKEN_COOKIE );
		if ( isset($_REQUEST['action']) && 'delauth' == $_REQUEST['action'] ) 
		{
			$consent = self::$mWLL->processConsent($_REQUEST);
			$token = $consent->getToken();
			setcookie( self::TOKEN_COOKIE, $token, time()+86400, '/', JW_HOSTNAME );
		}
		return $consent;
	}

	static public function GetToken($use_once=false)
	{
		$consent_token = @$_COOKIE[ self::TOKEN_COOKIE ];
		if ( null == $consent_token )
			return null;

		if ( true==$use_once )
			setcookie( self::TOKEN_COOKIE );

		self::Init();
		$token = self::$mWLL->processConsentToken($consent_token);	
		if ( $token && false==$token->IsValid() )
		{
			$token = null;
		}
		return $token;
	}

	static public function GetContactList($use_once=false)
	{
		$token = self::GetToken($use_once);
		if ( null == $token )
			return array();

		$dat = $token->getDelegationToken();
		$intlid = hexdec($token->getLocationId());

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
?>
