<?php
/**
 * @package  JiWai.de
 * @copyright   AKA Inc.
 * @author   seek@jiwai.com
 * @version  $Id$
 */

/**
 * JWBuddy_Mailbox
 */
abstract class JWBuddy_Mailbox {

	public static $mContactList = array();

	protected static $mSupportedSite = array('Netease', 'Sina', 'Yahoo', 'Sohu');
	
	public static $mUserAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	
	static public function GetContactList($user, $pass, $domain=null, $extra=array())
	{
		$instance = null;
		switch( $domain )
		{
			case '126.com':
			case '163.com':
				$instance = new JWBuddy_Mailbox_Netease();
				break;
			case 'yahoo.com.cn':
			case 'yahoo.cn':
				$instance = new JWBuddy_Mailbox_Yahoo();
				break;
			case 'sohu.com':
				$instance = new JWBuddy_Mailbox_Sohu();
				break;
			case 'sina.com':
				$instance = new JWBuddy_Mailbox_Sina();
				break;
			case 'gmail.com':
				$instance = new JWBuddy_Mailbox_Gmail();
				break;
			case 'qq.com':
				$instance = new JWBuddy_Mailbox_QQ();
				break;
			case 'live.cn':
			case 'live.com':
			case 'msn.com':
			case 'hotmail.com':
			case 'passport.com':
				$instance = new JWBuddy_Mailbox_Live();
				break;
			default: 
				return array();
		}
		
		return self::$mContactList = $instance->GetContactList($user, $pass, $domain, $extra);
	}

	static public function RenderContactList($user, $pass = '', $type = 'json') {
		self::GetContactList($user, $pass);
		$ret = null;

		switch ($type) {
			case "json" :
				$ret = json_encode(self::$mContactList);
			break;
			case "array" :
				$ret = serialize(self::$mContactList);
			break;
			default :
			break;
		}

		return $ret;
	}
}

/**
 * Class for retrieve contact list from mail.163.com,mail.126.com
 * support @163.com, @126.com
 */
class JWBuddy_Mailbox_Netease
{
	public function GetContactList($user, $pass, $domain=null) 
	{
		//Switch Login Script by domain
		switch ($domain) //use style=简约
		{
			case '163.com':
				$url_first = "http://reg.163.com/login.jsp?type=1&url=http://fm163.163.com/coremail/fcg/ntesdoor2?lightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D34";
				$post_data = 'username=' . urlencode($user) . '&password=' . urlencode($pass);
			break;
			case '126.com':
				$url_first = 'https://entry.mail.126.com/cgi/login?redirTempName=https.htm&hid=10010102&lightweight=1&verifycookie=1&language=0&style=11';
				$post_data = 'user=' . urlencode($user) . '&pass=' . urlencode($pass);
			break;
			default:
				return array();
		}
		$cookie_jar = tempnam('/tmp','cookie'); 
		
		//[1]. first login
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_first);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);

		if ( false == preg_match('/window.location.replace\("(.+)"\)/i', $content, $matches )
			&& false == preg_match('/HTTP-EQUIV=REFRESH CONTENT="0;URL=(.+)">/i', $content, $matches ) )
		{
			@unlink( $cookie_jar );
			return array();
		}

		//[2]. second redirect
		$url_redirect = $matches[1];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_redirect);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_exec($ch);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		/* get mail_host and mail_sid */
		$mail_host = $mail_sid = null;
		if ( preg_match( '/http:\/\/([^\/]+)\/(\S+)sid=(\w+)/', $effective_url, $matches ) )
		{
			$mail_host = $matches[1];
			$mail_sid = $matches[3];
		}
		if ( null==$mail_sid ) 
		{
			@unlink( $cookie_jar );
			return array();
		}

		//[3]. get address list 
		$mail_url = "http://$mail_host/coremail/fcg/ldvcapp?funcid=prtsearchres&sid=$mail_sid&ifirstv=&listnum=0&showlist=&tempname=address%2Faddress.htm";
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $mail_url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		$content = mb_convert_encoding($content, 'UTF-8', 'GB2312,UTF-8');
		curl_close($ch);

		$ret = array();
		if ( preg_match_all( '/-->(.*)\s*<\/td>\s*<td .+>.+-->(.+@.+)<\/a>/U', $content, $matches, PREG_SET_ORDER))
		{
			$ret = array();
			foreach( $matches as $one )
			{
				$ret[] = array(
					'nameScreen' => trim($one[1]),
					'email' => $one[2],
				);
			}
		}

		@unlink( $cookie_jar );
		return $ret;
	}
}

/**
 * Class for retrieve contact list from mail.sina.com.cn
 * support @sina.com
 */
class JWBuddy_Mailbox_Sina 
{
	public function GetContactList($user, $pass, $domain=null) 
	{
		$cookie_jar = tempnam('/tmp','cookie'); 

		//[1]. first login
		$url_first = "http://mail.sina.com.cn/cgi-bin/login.cgi";
		$post_data = 'logintype=uid&u='.urlEncode($user).'&psw='.urlDecode($pass);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_first);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);

		$content = curl_exec($ch);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		if ( false == preg_match( '/http:\/\/(.+.sinamail.sina.com.cn)/', $effective_url, $matches ) )
		{
			@unlink( $cookie_jar );
			return array();	
		}
		$mail_host = $matches[1];

		//[2]. get_csv_data
		$url_export = "http://$mail_host/classic/addr_export.php";
		$post_data = "extype=csv";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_export);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		curl_close($ch);
		
		//[3]. Retrieve Contact List
		$datas = preg_split("/\r|\n/", $content, -1, PREG_SPLIT_NO_EMPTY);
		array_shift( $datas );
		$ret = array();
		foreach( $datas as $one )
		{
			$one = explode(',', $one);
			$ret[] = array(
					'nameScreen' => $one[0],
					'email' => $one[3],
				      );
		}

		@unlink( $cookie_jar );
		return $ret;
	}
}

/**
 * Class for retrieve contact list from mail.yahoo.com.cn
 * support @yahoo.cn @yahoo.com.cn
 */
class JWBuddy_Mailbox_Yahoo 
{
	public function GetContactList($user, $pass, $domain=null) 
	{
		$cookie_jar = tempnam('/tmp','cookie'); 
		$login = $user . '@' . $domain;

		//[1]. first get challenge 
		$url_first = "http://cn.mail.yahoo.com/";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_first);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);

		if ( false == preg_match( '/name=".challenge" value="(.+)"/', $content, $matches ) )
		{
			@unlink($cookie_jar);
			return array();
		}
		
		//[2] login	
		$challenge = $matches[1];
		$passwd = md5( md5($pass) . $challenge );
		$url_login = "http://edit.bjs.yahoo.com/config/login?.intl=cn&.done=http%3A//cn.mail.yahoo.com/inset.html%3Frr%3D331164890&.src=ym&.cnrid=ymhp_20000&.challenge=$challenge&login=$login&passwd=$passwd&.persistent=&submit=%u767B%20%u5F55&.hash=1&.js=1&.md5=1";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_login);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);

		$content = curl_exec($ch);
		curl_close($ch);

		//[3]. address csv
		//[3.1]. get crumb value
		$url_export = "http://cn.address.mail.yahoo.com/index.php";	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_export);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		curl_close($ch);

		//Get crumb value for address list export;
		if ( false == preg_match( '/name=".crumb" id="crumb" value="(.+)"/U', $content, $matches ) )
		{
			@unlink($cookie_jar);
			return array();
		}
		$crumb = $matches[1];

		//[3.2] get csv file
		$post_data = "VPC=import_export&submit[action_export_outlook]=true&.crumb=$crumb";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_export);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		curl_close($ch);

		//[4]. retrieve contact list
		$datas = preg_split("/\r|\n/", $content, -1, PREG_SPLIT_NO_EMPTY);
		array_shift( $datas );

		$ret = array();
		foreach( $datas as $one )
		{
			$one = explode(',', str_replace('"', '', $one) );
			$ret[] = array(
					'nameScreen' => "$one[3] $one[1]",
					'email' => $one[55],
				      );
		}

		@unlink( $cookie_jar );
		return $ret;
	}
}

/**
 * Class for retrieve contact list from mail.sohu.com
 * support @sohu.com
 */
class JWBuddy_Mailbox_Sohu
{
	public function GetContactList($user, $pass, $domain=null) 
	{
		$cookie_jar = tempnam('/tmp','cookie'); 
		
		$userid = urlencode($user. '@' . $domain);
		$password = md5($pass);

		//[1]. login sohu passport
		$url_passport = "http://passport.sohu.com/sso/login.jsp?userid=$userid&password=$password&appid=1000&persistentcookie=0&s=1207358663646&b=1&w=1024&pwdtype=1";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_passport);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);

		$content = curl_exec($ch);
		curl_close($ch);

		if ( false == preg_match( '/success/', $content ) )
		{
			@unlink( $cookie_jar );
			return array();
		}
		
		//[2]. sohu maillogin
		$url_maillogin = "http://login.mail.sohu.com/servlet/LoginServlet";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_maillogin);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$content = curl_exec($ch);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		preg_match( '/http:\/\/([^\/]+)/', $effective_url, $matches );
		$mail_host = $matches[1];

		//[3]. sohu addresslist get;
		$url_address = "http://$mail_host/control/addressbook?act=0&ob=nickname&o=true&f=0";
		$page_no = 1;

		$ret = array();
		while (true) 
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url_address);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar); 
			curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$content = curl_exec($ch);
			curl_close($ch);
			
			$ret = $this->GetListFromContent($content, $ret);
			
			//next_page_url_builder
			$page_no++;
			$reg_string = '/addressbook\?act=0&ob=nickname&o=true&f=(\d+)">'.$page_no.'</i';
			if ( false == preg_match($reg_string, $content, $matches))
				break;

			$url_address = "http://$mail_host/control/addressbook?act=0&ob=nickname&o=true&f=$matches[1]";
		}

		@unlink( $cookie_jar );
		return $ret;
	}
		
	function GetListFromContent( $content, $ret=array() )
	{
		if ( preg_match_all('/value="&quot;(.*)&quot;&lt;(.+@.+)&gt;"/U', $content, $matches, PREG_SET_ORDER) )
		{
			foreach( $matches AS $one )
			{	
				$ret[] = array(
					'nameScreen' => $one[1],
					'email' => $one[2],
				);
			}
		}
		return $ret;
	}
}

/**
 * Class for retrieve contact list from Windows Live;
 * support @live.com, @live.cn, @msn.com, @hotmail.com, @passport.com
 */
class JWBuddy_Mailbox_Live
{
	public function GetContactList($user, $pass, $domain=null) 
	{
		$cookie_jar = tempnam('/tmp','cookie'); 
		
		$login = "$user@$domain";

		//[1]. get hidden param of mail.live.com
		$url_mailindex = "http://mail.live.com/";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_mailindex);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);

		//[2]. post user and pass value;
		preg_match('/name="PPFT".+value="(.+)"\/>/iU', $content, $matches);
		$ppft = $matches[1];
		preg_match("/srf_uPost='(.+)'/iU", $content, $matches);
		$url_postlogin = $matches[1];
		$post_data = "LoginOptions=3&NewUser=0&login=$user@$domain&passwd=$pass&PadPad=IfYouAreReadingThisYouHaveTooMuch&PPSX=Passp&PPFT=$ppft&wa=wsignin1.0";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_postlogin);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);
		
		//[3]. post and redirect to mail succ;
		//msn.com will need this step
		if ( preg_match('/action="(.+)"/iU', $content, $matches) )
		{
			$url_postmail = $matches[1];
			$post_data = "wa=wsignin1.0";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url_postmail);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
			$content = curl_exec($ch);
			curl_close($ch);
		}

		if ( false == preg_match( '/window.location.replace\("(.+)"\);/iU', $content, $matches ) )
		{
			@unlink( $cookie_jar );
			return array();
		}
		$url_mailsucc = $matches[1];

		//[4]. go mail succ page;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_mailsucc);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		//[5]. get mail contact page;
		preg_match('/http:\/\/([^\/]+)/', $effective_url, $matches);
		$mail_host = $matches[1];
		$url_address = "http://$mail_host/mail/ContactMainLight.aspx?n=".time();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_address);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);  
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);
		$content = str_replace('&#64;', '@', $content);

		preg_match_all('/>\s*([\w-_]+@[\w\.-]+[\w-]+)\s*</iU', $content, $matches, PREG_SET_ORDER);

		$ret = array();
		foreach( $matches as $one )
		{
			$ret[] = array(
				'nameScreen' => $one[1],
				'email' => $one[1],
			);
		}

		@unlink( $cookie_jar );
		return $ret;
	}
}

/**
 * Class for retrieve contact list from GoogleMail
 * support @gmail.com
 */
class JWBuddy_Mailbox_Gmail
{
	public function GetContactList($user, $pass, $domain=null) 
	{

		$cookie_jar = tempnam('/tmp','cookie'); 
		
		//[1]. google auth
		$login = urlencode($user);
		$password = urlencode($pass);
		$url_login = "https://www.google.com/accounts/ServiceLoginAuth?service=mail";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_login);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "Email=$login&Passwd=$password&PersistentCookie=");
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$content = curl_exec($ch);
		curl_close($ch);

		if ( preg_match('/class="errormsg"/i', $content ) )
		{	
			@unlink( $cookie_jar );
			return array();
		}

	/** no need;
		//[2]. mail.google , html version, not standard version, no js;
		$login = urlencode($user);
		$password = urlencode($pass);
		$url_login = "https://mail.google.com/mail/?ui=html";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_login);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$content = curl_exec($ch);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		$host_prefix = substr($effective_url, 0, strpos( $effective_url, '?' ) );
		$url_address = $host_prefix . '?ui=html&v=cl&pnl=a&f=1'; //all, contact list
	*/

		//[3]. get contact list;
		$host_prefix = "https://mail.google.com/mail/h/";
		$url_address = $host_prefix . '?ui=html&v=cl&pnl=a&f=1'; //all, contact list

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_address);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);

		//retrive contact list;		
		$ret = array();
		
		$reg_string = '/<td>\s*<b>([^><]+)<\/b>\s*<\/td>\s*<td>\s*([\S]+@[\S]+)\s+.+<\/td>/U';
		if ( preg_match_all($reg_string, $content, $matches, PREG_SET_ORDER) )
		{
			foreach( $matches AS $one )
			{
				$ret[] = array(
					'nameScreen' => $one[1],
					'email' => $one[2],
				);
			}
		}
		
		@unlink( $cookie_jar );
		return $ret;
	}
}

/**
 * Class for retrieve contact list from mail.qq.com
 * support @qq.com
 */
class JWBuddy_Mailbox_QQ
{
	public function GetContactList($user, $pass, $domain=null, $extra=array()) 
	{

		$cookie_jar = tempnam('/tmp','cookie'); 
		
		$ts = $extra['ts'];
		$p = urlencode($extra['p']);
		$starttime = $ts*1000 + rand(10000,20000);

		//[1]. url_first
		$url_first = "http://mail.qq.com/";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_first);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);

		$content = curl_exec($ch);
		curl_close($ch);

		preg_match('/action="(.+)"/U', $content, $matches);

		//[2]. qqmail login
		$uin = urlencode($user);
		$url_login = $matches[1];
		$post_data = "verifycode=&subtmpl=&starttime=$starttime&sid=0,zh_CN&redirecturl=&from=&uin=$uin&p=$p&ts=$ts&frametype=html&firstlogin=false&delegate_url=&pp=000000000";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_login);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);

		$content = curl_exec($ch);
		curl_close($ch);

		if( false == preg_match("/t='0; url=http:\/\/([^\/]+)\/\S+sid=(\S+)&target=today'/i", $content, $matches))
		{
			@unlink( $cookie_jar );
			return array();
		}
		
		//[3]. qq address page
		$mail_host = $matches[1];
		$mail_sid = $matches[2];
		$url_address = "http://$mail_host/cgi-bin/addr_listall?sid=$mail_sid";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_address);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_USERAGENT, JWBuddy_Mailbox::$mUserAgent);
		$content = curl_exec($ch);
		curl_close($ch);

		//retrieve contact list;
		$content = mb_convert_encoding( $content, 'UTF-8', 'GB2312' );
		$reg_string = '/<p class="L_n">&nbsp;(.+)<\/p>\s*<p class="L_e"><span>(.+@.+)<\/span>&nbsp;<\/p>/iU';

		$ret = array();
		if ( preg_match_all( $reg_string, $content, $matches, PREG_SET_ORDER ) )
		{
			foreach ( $matches AS $one )
			{
				$ret[] = array(
					'nameScreen' => $one[1],
					'email' => $one[2],
				);
			}
		}
		
		@unlink( $cookie_jar );
		return $ret;
	}
}
?>
