<?php
/**
 * Common header of JiWai.de classes
 * Note: should be included at the very beginning of each application.
 *
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      zixia@zixia.net
 * @version     $Id$
 */

/**
 * Path of JiWai.de
 *
 */
define('JW_ROOT',     dirname(__FILE__) . '/');

/**
 * Path of configuration
 *
 */
define('CONFIG_ROOT',	JW_ROOT . 'config/');

/**
 * Path of web modules
 *
 */
define('CLASS_ROOT',	JW_ROOT . 'class/');

/**
 * Path of web modules
 *
 */
define('LIB_ROOT',	JW_ROOT . 'lib/');

/**
 * Path of cache
 *
 */
define('CACHE_ROOT',	'/var/cache/tmpfs/jiwai/');

/**
 * Filter Dict File
 */
define('FILTER_DICT',  JW_ROOT . 'webroot/wo/zdmin/dictionary/filterdict.txt');


/**
 * Path of config cache
 *
 */
// XXX by zixia 2006-06-15 临时测试 
//define('CONFIG_CACHE',	"/tmp/alpha.xml.php");
define('CONFIG_CACHE',	CACHE_ROOT . 'config/config.xml.php');


/**
 * Path of err log
 *
 */
define('ERROR_LOGFILE',	CACHE_ROOT . 'err/ERR_{Date}_{Type}_{Code}.log');

define('EXCEPTION_LOG', true);
define('EXCEPTION_DISPLAY', false);
define('ERROR_LOG', true);
define('ERROR_DISPLAY', false);

if (!defined('CONSOLE'))
define('CONSOLE', !isset($_SERVER['REQUEST_URI']));

define('DEBUG',	true);
define('DEBUG_LOGFILE', CACHE_ROOT . '/debug/DBG_{Date:YmdH}.log');
//define('DEBUG_DISPLAY_MODE',	'JSLOG');
define('DEBUG_LOG',	true);


if (file_exists(CONFIG_CACHE)){
       	require_once CONFIG_CACHE;
}else{
	JWConfig::Instance();
	$config_data=JWConfig::dump();
$str=<<<STR
<?php
define('JW_CONFIG_DATA','$config_data');
?>
STR;
       	file_put_contents(CONFIG_CACHE,$str);
}

/**
 * Autoload Class File
 *
 * @param string $class_name
 */
function __autoload($class_name) {
	if (0===strpos($class_name,'JW'))
		$file = CLASS_ROOT;
	else
		$file = LIB_ROOT;

	$file .= $class_name . '.class.php';

	$file = str_replace('_','/',$file);

	require_once $file;

    if (!class_exists($class_name) && !interface_exists($class_name) ) {
        throw new JWException($class_name.' not found.');
    }
}

//require_once 'JWDebug.class.php';
//require_once 'class/JWException.class.php';

if (!defined('NO_SESSION')) JWSession::Instance();

// use for strftime
setlocale(LC_ALL, 'zh_CN.UTF-8');
mb_internal_encoding("UTF-8");

//for Lighty
if (empty($_SERVER['SCRIPT_URI']) && !empty($_SERVER['REQUEST_URI'])) {
	$_SERVER['SCRIPT_URI'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}



?>
