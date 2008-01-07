<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@jiwai.com
 * @version		$Id$
 */

/**
 * JiWai.de Internationalization(I18n) Class
 */
class JWI18n {

    /**
     * Text Domain of the Application
     * 
     */
    static private $msTextDomain = 'messages';

	/**
	 * Instance of this singleton
	 *
	 * @var JWI18n
	 */
    static private $msInstance = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWI18n
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
		bindtextdomain(self::$msTextDomain, LOCALE_ROOT);
		textdomain(self::$msTextDomain);
	}

	/**
	 * Set the Locale
	 *
	 */
	function SetAppLocale($locale = 'default')
	{
		setlocale(LC_ALL, $locale);
	}

}
