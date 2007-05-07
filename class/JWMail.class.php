<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

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
	 * Instance of this singleton class
	 *
	 * @return JWFile
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
		$config 	= JWConfig::instance();
		$directory 	= $config->directory;

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
	static private function EscapeHeadString($string,$encoding='UTF-8')
	{
		if ( 'UTF-8'!=$encoding )
			$string	= mb_convert_encoding($string, $encoding, "UTF-8");

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
		if ( !isset($options['encoding']) )
			$options['encoding'] 	= 'UTF-8';

		if ( !isset($options['contentType']) )
			$options['contentType'] = 'text/plain';


		if ( 'UTF-8'!=$options['encoding'] )
			$message = mb_convert_encoding($message, $options['encoding'], 'UTF-8');

		$message	= chunk_split(base64_encode($message),70);

		$subject 	= self::EscapeHeadString($subject	,$options['encoding']);
		$from		= self::EscapeHeadString($from		,$options['encoding']);
		$to			= self::EscapeHeadString($to		,$options['encoding']);

		$headers = 	
 			 'Mime-Version: 1.0'
			."\r\n" . "Content-type: $options[contentType]; charset=$options[encoding]"
			."\r\n" . "Content-Transfer-Encoding: BASE64"
			."\r\n" . 'X-Mailer: JWMailer/1.0'
 			."\r\n" . "From: $from"
			."\r\n" . "Reply-To: $from"
			;

		if ( isset($options['messageId']) )
			$headers .= "\r\n" . "Message-Id: <$options[messageId]>";

		$headers .= "\r\n";


		return mail($to, $subject, $message, $headers);
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
							 '/%User.nameScreen%/i'	=>	$user['nameScreen']
							,'/%User.nameFull%/i'	=>	$user['nameFull']

							,'/%Friend.nameScreen%/i'=>	$friend['nameScreen']
							,'/%Friend.nameFull%/i'	=>	$friend['nameFull']
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

		$template_file	= 'NoticeNewFriend.html';

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

}
?>
