<?php
ini_set('include_path', JW_ROOT.'lib/OpenID'.':'.ini_get('include_path'));

/**
 * @package		JiWai.de
 * @author	  	freewizard
 */

/**
 * JiWai.de OpenID Class
 */
class JWOpenID {
	static function &GetStore() {
		$s = new JWOpenID_Store();
		return $s;
	}
	/**
	 * Instance of this singleton
	 *
	 * @var JWOpenID
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWOpenID
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	/*
	 *	创建 内部idUser / OpenID Identifier 配对
	 */
	static public function Create($urlOpenid,$idUser)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$urlOpenid 	= self::GetCoreUrl($urlOpenid);
		return JWDB::SaveTableRow('Openid', array(	 'idUser'		=> $idUser
													,'urlOpenid'	=> $urlOpenid
													,'timeCreate'	=> JWDB::MysqlFuncion_Now()
												));
	}

	/*
	 *	删除 内部idUser / OpenID Identifier 配对
	 */
	static public function Destroy($idOpenid)
	{
		$idOpenid 	= JWDB::CheckInt($idOpenid);

		return JWDB::DelTableRow('Openid', array( 'id'=>$idOpenid ));
	}


	/*
	 *	@param	int		$idUser
	 *	@return	int		$idOpenid null 代表没有这个 openid
	 */
	static public function GetIdByUserId($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$row = JWDB::GetTableRow('Openid', array('idUser'=>$idUser));

		if ( empty($row) )
			return null;

		return $row['id'];
	}


	/*
	 *	@param	string	$urlOpenid	openid
	 *	@return	int		$idOpenid	null 代表没有这个 openid
	 */
	static public function GetIdByUrl($urlOpenid)
	{
		$core_url = JWOpenID::GetCoreUrl($urlOpenid);
		$row = JWDB::GetTableRow('Openid', array('urlOpenid'=>$core_url));

		if ( empty($row) )
			return null;

		return $row['id'];
	}

	static public function IsPossibleOpenID($usernameOrEmail)
	{ //FIXME: xxx.xx could be a username
		if ( preg_match('#jiwai\.de#', $usernameOrEmail)) return false;
		if ( preg_match('#\.#', $usernameOrEmail)			// 有 . 则可能是域名
				&& !preg_match('#@#',$usernameOrEmail) )	//	排除 @ ，代表 email
			return true;
	
		return false;
	}

	static public function GetCoreUrl($url)
	{
		//$url = preg_replace('#^\w://#', '', $url);
		$url = preg_replace('/#.+$/', '', $url);
		return $url;
	}

	static public function GetFullUrl($url)
	{
		//if ( !preg_match('#^\w+://#i',$url) )
		//	$url = 'http://' . $url;
		return $url;
	}

	/*
	 *	根据 idOpenids 获取 Row 的详细信息
	 *	@param	array	idOpenids
	 * 	@return	array	以 idOpenid 为 key 的 db row
	 * 
	 */
	static public function GetDbRowsByIds ($idOpenids)
	{
		if ( empty($idOpenids) )
			return array();

		if ( !is_array($idOpenids) )
			throw new JWException('must array');

		$idOpenids = array_unique($idOpenids);

		$condition_in = JWDB::GetInConditionFromArray($idOpenids);

		$sql = <<<_SQL_
SELECT
		id as idOpenid
		, idUser
		, urlOpenid
		, UNIX_TIMESTAMP(timeCreate) AS timeCreate
FROM	Openid
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$openid_db_rows = array();
		} else {
			foreach ( $rows as $row ) {
				$openid_db_rows[$row['idOpenid']] = $row;
			}
		}

		return $openid_db_rows;
	}

	static public function GetDbRowById ($idOpenid)
	{
		$openid_db_rows = JWOpenID::GetDbRowsByIds(array($idOpenid));

		if ( empty($openid_db_rows) )
			return array();

		return $openid_db_rows[$idOpenid];
	}


	static public function IsUserOwnId($idUser, $idOpenid)
	{
		$db_row = JWOpenID::GetDbRowById($idOpenid);

		return $db_row['idUser']==$idUser;
	}

	static public function &GetConsumer() {
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/SReg.php";
		require_once "Auth/OpenID/PAPE.php";
		global $pape_policy_uris;
		$pape_policy_uris = array(
			  PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
			  PAPE_AUTH_MULTI_FACTOR,
			  PAPE_AUTH_PHISHING_RESISTANT
			  );
		$c = new Auth_OpenID_Consumer(JWOpenID::GetStore());
		return $c;
	}

	static public function AuthRedirect($openid) {
		$consumer = self::GetConsumer();

		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($openid);

		// No auth request means we can't begin OpenID.
		if (!$auth_request) {
			self::DisplayError("Authentication error; not a valid OpenID.");
		}

		$sreg_request = Auth_OpenID_SRegRequest::build(
									 // Required
									 array('nickname'),
									 // Optional
									 array('fullname', 'email'));

		if ($sreg_request) {
			$auth_request->addExtension($sreg_request);
		}

		$policy_uris = $_GET['policies'];

		$pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
		if ($pape_request) {
			$auth_request->addExtension($pape_request);
		}

		// Redirect the user to the OpenID server for authentication.
		// Store the token for this authentication so we can verify the
		// response.

		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if ($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL('http://'.$_SERVER['HTTP_HOST'], //Trust Root URL
												   'http://'.$_SERVER['HTTP_HOST'].'/wo/openid/consumer/finish_auth' //Return To URL
				);

			// If the redirect URL can't be built, display an error
			// message.
			if (Auth_OpenID::isFailure($redirect_url)) {
				self::DisplayError("Could not redirect to server: " . $redirect_url->message);
			} else {
				// Send redirect.
				header("Location: ".$redirect_url);
			}
		} else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			$form_html = $auth_request->formMarkup(
				'http://'.$_SERVER['HTTP_HOST'], //Trust Root URL
				'http://'.$_SERVER['HTTP_HOST'].'/wo/openid/consumer/finish_auth', //Return To URL
				false, array('id' => $form_id));

			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (Auth_OpenID::isFailure($form_html)) {
				self::DisplayError("Could not redirect to server: " . $form_html->message);
			} else {
				$page_contents = array(
			   "<html><head><title>",
			   "OpenID transaction in progress",
			   "</title></head>",
			   "<body onload=\"document.getElementById('$form_id').submit();document.getElementById('btn').value='Connecting...';document.getElementById('btn').disabled='disabled';\">",
			   $form_html,
			   "</body></html>");

				print implode("\n", $page_contents);
				exit();
			}
		}
	}
	static function DisplayError($s) {
		JWSession::SetInfo('error', $s);
		header('Location /');
		exit();
	}
}
?>
