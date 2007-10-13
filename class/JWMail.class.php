<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * Pear::Mail
 */
require_once('Mail.php');

/**
 * JiWai.de Mail Class
 */
class JWMail {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMail
	 */
	static private $msInstance;

	/**
	 * path_config
	 *
	 * @var
	 */
	static private $msTemplateRoot = null;

	/**
	 * Pear::Mail Object
	 *
	 * @var
	 */
	static private $msMailObject = null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWMail
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
		$config 	= JWConfig::Instance();
		$directory 	= $config->directory;

        self::$msMailObject     =   & Mail::factory('smtp');
		self::$msTemplateRoot	= 	$directory->mail->template ;

		if ( ! file_exists(self::$msTemplateRoot) ){
			throw new JWException( "dir not exist [" . self::$msTemplateRoot . "]");
		}
	}


	/*
	 *	将邮件头中的字符串做 =?UTF-8?B?BASE64?= 的转义
	 *	@param	string	string 是需要转义的字符串
	 *	@param	string	encoding	编码，缺省为UTF-8
	 *	@return	string	转义过的字符串
	 */
	static private function EscapeHeadString($string,$encoding='UTF-8', $force=false )
	{
		if ( 'UTF-8'!=$encoding )
			$string	= mb_convert_encoding($string, $encoding, "UTF-8");

        if( $force ) 
            return '=?' . $encoding . '?B?'. base64_Encode($string) .'?=';

		return preg_replace_callback
			(
					 '/([\x80-\xFF]+.*[\x80-\xFF]+)/'
					,create_function
					(
						 '$matches'
						,"return \"=?$encoding?B?\".base64_encode(\$matches[1]).\"?=\";"
					)
					,$string
			);
	}

	
	/*
	 *	发送邮件的最后一步
	 *	@param options	
						contentType		= true
						messageId		= null
						encoding		= 'UTF-8'
 	 *
 	 *
 	 *
	 *
	 */
	static function SendMail($from, $to, $subject, $message, $options=null)
	{

        self::Instance();

		if ( !isset($options['encoding']) )
			$options['encoding'] 	= 'GB2312';

		if ( !isset($options['contentType']) )
			$options['contentType'] = 'text/plain';


		$message_head = preg_replace("/\n/s",' ',$message);
		$message_head = substr($message_head,0,40);
		JWLog::Instance()->Log(LOG_INFO,"JWMail::SendMail($from, $to, $subject,...)");


		if ( 'UTF-8'!=$options['encoding'] )
			$message = mb_convert_encoding($message, $options['encoding'], 'UTF-8');

		$message	= chunk_split(base64_encode($message));

		$subject 	= self::EscapeHeadString($subject	,$options['encoding'], true);
		$from		= self::EscapeHeadString($from		,$options['encoding']);
		$to			= self::EscapeHeadString($to		,$options['encoding']);

        $headers = array(
            'Mime-Version'  => '1.0',
            'Content-Type'  => "$options[contentType]; charset=$options[encoding]",
            'Content-Transfer-Encoding' => 'base64',
            'X-Mailer'      => 'JMWailer/1.0',
            'From'          => $from,
            'To'            => $to,
            'Subject'       => $subject,
        );

		if ( isset($options['messageId']) )
			$headers["Message-Id"] = "<$options[messageId]>";

        return self::$msMailObject->send($to, $headers, $message);
	}

	/*
	 *	渲染邮件模板的通用部分，注意 User 是收件人。替换的宏包括：
					User.nameScreen
					User.nameFull
					Friend.nameScreen
					Friend.nameFull
 	 *	@param	string	template	模板
	 *	@return	string	template	render好的模板
	 */
	static private function RenderTemplate($template, $user, $friend)
	{
		$replace_array	= array (
					 '/%User.nameScreen%/i'	=> $user['nameScreen'],
					 '/%EUser.nameScreen%/i' => UrlEncode($user['nameScreen']),
					 '/%User.nameFull%/i' => $user['nameFull'],

					 '/%Friend.nameScreen%/i' => @$friend['nameScreen'],
					 '/%EFriend.nameScreen%/i' => UrlEncode($friend['nameScreen']),
					 '/%Friend.nameFull%/i'	=> @$friend['nameFull'],
				);

		return preg_replace(	 array_keys		($replace_array)
								,array_values	($replace_array)
								,$template
							);
	}

	
	/*
	 *	获取模板文件内容
	 *	@param	string	relTemplateFile	模板文件的相对路径
	 *	@return	string	内容 
	 */
	static private function LoadTemplate($relTemplateFile)
	{
		self::Instance();

		$template_abs_path	= self::$msTemplateRoot . $relTemplateFile;

		$file_content = file_get_contents($template_abs_path);

		if ( empty($file_content) )
			throw new JWException("no template found at [$template_abs_path]");

		return $file_content;
	}


	/*
	 *	将模板文件进行初步解析，分离 META 和 HTML 并返回
	 *	@param	string	relTemplateFile	模板文件的相对路径
	 *	@return	array	array ( 'html'	=> '', 'subject' => '', 'from' => '' )
	 */
	static private function ParseTemplate($templateData)
	{
		$template_info = array();

		if ( !preg_match('/^(.+?)[\r\n]{2}(.+)$/s',$templateData,$matches) )
			throw new JWException("template split meta & body error for [$templateData]");

		$template_info['html']	= $matches[2];
		$meta_lines		= split("\n", $matches[1]);

		foreach ( $meta_lines as $meta_line )
		{
			if ( ! preg_match('/^([^:]+):\s*(\S.*)$/',trim($meta_line),$matches) )
				throw new JWException("template header meta parse error");

			$key = strtolower($matches[1]);
			$val = $matches[2];
			$template_info[$key] = $val;
		}

		return $template_info;
	}


	/*
	 *	$user 将 $friend 新加为好友，给 $friend 发送一封通知信
 	 *	@param	array	user	user_info的结构
 	 *	@param	array	friend	user_info的结构
 	 */
	static public function SendMailNoticeNewFriend($user, $friend)
	{
		if ( !JWUser::IsValidEmail($friend['email']) )
			return;

		$template_file	= 'NoticeNewFriend.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user,$friend);
	

		$template_info = self::ParseTemplate($template_data);

//die(var_dump($template_info));
		
		return self::SendMail(	 $template_info['from']
						,$friend['email']
						,$template_info['subject']
						,$template_info['html']
					);
	}


	/*
	 *	$user 向 $mails 发送邀请注册信
 	 *	@param	array	user	user_info的结构
 	 *	@param	string	email	邮件接收者
 	 */
	static public function SendMailInvitation($user, $email, $message, $code)
	{
		if ( !JWUser::IsValidEmail($email) )
			return false;

		$friend['nameFull'] = '敬启者';

		$template_file	= 'Invitation.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user, $friend);
	
		$template_data = preg_replace('/%INVITATION_ID%/i'	,$code		,$template_data);
		$template_data = preg_replace('/%MESSAGE%/i'		,$message	,$template_data);
		$template_data = preg_replace('/%SUBJECT%/i'		,$message	,$template_data);
		$template_data = preg_replace('/%DATE%/i'		    ,date('m/d/Y'), $template_data);

		$template_info = self::ParseTemplate($template_data);

//die(var_dump($template_info));
		
		return self::SendMail(	 $template_info['from']
						,$email
						,$template_info['subject']
						,$template_info['html']
					);
	}


	/*
	 *	$sender 发送悄悄话 $message 给 $receiver，并给 $friend 发送一封通知信
 	 *	@param	array	sender		user_info的结构
 	 *	@param	array	receiver	user_info的结构
 	 *	@param	string	message		direct message
 	 */
	static public function SendMailNoticeDirectMessage($sender, $receiver, $message, $device)
	{
		if ( !JWUser::IsValidEmail($receiver['email']) )
			return;

		$template_file	= 'NoticeDirectMessage.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$receiver,$sender);
	
		$template_data = preg_replace('/%DirectMessage.message%/i'	,$message	,$template_data);
		$template_data = preg_replace('/%DirectMessage.device%/i'	,$device	,$template_data);

		$template_info = self::ParseTemplate($template_data);

//die(var_dump($template_info));
		return self::SendMail(	 $template_info['from']
						,$receiver['email']
						,$template_info['subject']
						,$template_info['html']
					);
	}


	/*
	 *	向 $user 发送重置密码的邮件
 	 *	@param	array	user		user_info的结构
 	 *	@param	string	secret		密码
 	 */
	static public function ResendPassword($user, $url)
	{
		if ( !JWUser::IsValidEmail($user['email'],true) )
			return;

		$template_file	= 'ResetPassword.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user,$user);
	
		$template_data = preg_replace('/%RESET_PASSWORD_URL%/i'	,$url	,$template_data);

		$template_info = self::ParseTemplate($template_data);

//die(var_dump($template_info));
//		die( " from - " . $template_info['from'] );
		return self::SendMail(	 $template_info['from']
						,$user['email']
						,$template_info['subject']
						,$template_info['html']
					);
	}


}
?>
