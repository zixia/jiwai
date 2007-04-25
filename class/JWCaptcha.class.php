<?php
require(JW_ROOT.'/lib/Captcha/Captcha.inc.php');
/**
 * 校验码工具类
 * 
 */
class JWCaptcha{
	/**
	 * 输出校验码图片二进制
	 */
	static public function image()
	{
		$oVisualCaptcha = new PhpCaptcha(array(JW_ROOT.'/lib/Captcha/VeraBd.ttf', JW_ROOT.'/lib/Captcha/VeraIt.ttf', JW_ROOT.'/lib/Captcha/Vera.ttf'), 200, 60);
		#$oVisualCaptcha->UseColour(true);
		#$oVisualCaptcha->DisplayShadow(true);
		$oVisualCaptcha->Create();
	}

	/**
	 * 输出声音二进制
	 */
	static public function audio()
	{
		$oAudioCaptcha = new AudioPhpCaptcha('/usr/bin/flite', CACHE_ROOT.'/captcha/');
		$oAudioCaptcha->Create();
	}

	/**
	 * 校验
	 *
	 * @param String $text 输入文本
	 * @return boolean true通过 false不通过
	 */
	static public function validate($text){		
		return PhpCaptcha::Validate($text);
	}	
}

?>
