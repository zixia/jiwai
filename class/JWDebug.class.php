<?php
/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      zixia@zixia.net
 * @version     $Id$
 */

if (!defined('DEBUG'))
/**
 *  Debug flag, true DEBUG ON (default) / false DEBUG OFF
 *
 */
define('DEBUG', true);

if (DEBUG) {
    if (!defined('DEBUG_DISPLAY')) {
		/**
		 * Log to frontend(browser/console)?
		 *
		 */
		define('DEBUG_DISPLAY', true);
    }
    if (!defined('DEBUG_DISPLAY_MODE')) {
		/**
		 * Mode of debug display
		 * Available value: 'NONE' / 'CONSOLE' / 'JSLOG' / 'NATIVE' / 'XML'
		 *
		 */
		define('DEBUG_DISPLAY_MODE', DEBUG_DISPLAY ? (CONSOLE ? 'CONSOLE' : 'NATIVE') : 'NONE');
    }
    if (!defined('DEBUG_LOG')) {
		/**
		 * Log to file?
		 *
		 */
		define('DEBUG_LOG', false);
    }
    if (!defined('DEBUG_LOGFILE')) {
		/**
		 *  Log file name
		 *
		 */
		define('DEBUG_LOGFILE', '/tmp/DBG_{Date:YmdH}.log');
    }
    /**
     *  JWDebug Class
     */
    class JWDebug {
		static private $instance;
		static private $timeStart;
		static private $varStart;
		static private $memStart;
		static private $memMax = 0;
		static private $debug_switch = 0;
		static private $debug_buffer = '';
		static private $logfile;
		static private $forceFlush;
		/**
		 * Return JWDebug instance, donot call it.
		 *
		 * @return JWDebug
		 */
		static function &instance ()
		{
		    if (!isset(self::$instance)) {
				$class = __CLASS__;
				self::$instance = new $class;
		    }
		    return self::$instance;
		}

		/**
		 * Constructing method, save initial state
		 *
		 */
		function __construct()
		{
		    self::$timeStart = self::microtime_float();
		    self::$memStart = function_exists('memory_get_usage') ? memory_get_usage() : -1;
		    @self::$varStart = count($GLOBALS);
		    if (DEBUG_LOG) {
				self::$logfile = preg_replace_callback('#{([^{}]+)}#', array('JWDebug', '__fillInfo'), DEBUG_LOGFILE);
				if (CONSOLE) {
				    error_log(sprintf("%s %d %s\n", date(DATE_ATOM), getmypid(), JWConsole::cmdline()), 3, self::$logfile);
				} else {
				    error_log(sprintf("%s %d %s %s referer %s\n", date(DATE_ATOM), getmypid(), $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"None"), 3, self::$logfile);
				}
		    }
		    self::write('JiWai.de debugger loaded. $Rev$');
		    if (CONSOLE) JWDebug::init();
		}
		static function __fillInfo($m) {
		    $s = '';
		    switch (strtolower($m[1])) {
				case 'cmd':
				case 'uri':
				    $s = urlencode(CONSOLE ? JWConsole::cmdline() : $_SERVER['REQUEST_URI']);
				    break;
				case 'referer':
				    $s = urlencode(CONSOLE ? '' : isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "None");
				    break;
				case 'pid':
				    $s = getmypid();
				    break;
				case 'date':
				    $s = date('Ymd');
				    break;
				default:
				    $v = explode(':', $m[1]);
				    switch (strtolower($v[0])) {
						case 'date':
						    $s = date($v[1]);
						    break;
						case 'random':
						    $s = rand(0, $v[1]-1);
						    break;
				    }
		    }
		    return $s;
		}
		/**
		 * Destructing method, write everything left
		 *
		 */
		function __destruct()
		{
		    $s = function_exists('memory_get_usage') ? memory_get_usage() : -1;
		    $t = self::microtime_float() - self::$timeStart;
	    $t = intval($t*100) / 100;
		    self::write('JiWai.de debugger quitted. ('.$s.'-'.self::$memStart.'='.($s - self::$memStart).' bytes, '.$t.' ms)');
		}
	/**
	 * Get current micro seconds.
		 * @return current ms.
		 */
	static private function microtime_float()
	{
    		list($usec, $sec) = explode(" ", microtime());
    		return 1000*(((float)$usec + (float)$sec));
	}

       /**
		 * Encode $msg according to DEBUG_DISPLAY_MODE
		 *
		 * @param string $msg
		 * @return string
		 */
		static private function encode($msg) {
		    switch (DEBUG_DISPLAY_MODE) {
				case 'NATIVE':
				    $msg = "<script type='text/javascript'>__LOG__('"
						.str_replace(array("\n", "\r"), array("\\n", "\\r"), addslashes($msg))."');</script>\n";
				    break;
				case 'JSLOG':
				    $msg = "<script type='text/javascript'>debug('"
						.str_replace("\n", "\\n", addslashes($msg))."');</script>\n";
				    break;
				case 'XML':
				   $msg = "<!-- DEBUG \n".addslashes($msg)."\n -->";
				    break;
				case 'CONSOLE':
				    $msg = JWConsole::convert("%K").$msg.JWConsole::convert("%n")."\n";
				    break;
				default:
				    $msg = '';
		    }
		    return $msg;
		}
		static function log() {
		    $arg_list = func_get_args();
		    foreach($arg_list as $a) self::write($a);
		}
		/**
		 * Write $msg to log window.
		 *
		 * @param string $msg
		 */
		static function write($msg=null)
		{
//XXX by zixia
return;
		    if (DEBUG_LOG) error_log(sprintf("%s %d %s\n", date(DATE_ATOM), getmypid(), $msg), 3, self::$logfile);
		    $msg = is_null($msg) ? '' : self::encode($msg);
		    if (self::$debug_switch!=1) {
				self::$debug_buffer.= $msg;
echo "msg: $msg\n";
echo "len: " . strlen(self::$debug_buffer) . "\n";
				return;
		    } else {
				if (self::$debug_buffer!='') {
				    $msg = self::$debug_buffer.$msg;
				    self::$debug_buffer = '';
				}
		    }
		    self::o($msg);
		}
		/**
		 * Write $msg to output in raw format.
		 *
		 * @param string $msg
		 */
		static function o($msg='')
		{
		    if (CONSOLE) {
				fwrite(STDERR, $msg);
				if ( self::$forceFlush ) fflush(STDERR);
		    } else {
				print $msg;
				if ( self::$forceFlush ) flush();
		    }
		}
		/**
		 * Write debug trace stack to log window.
		 *
		 */
		static function trace()
		{
//XXX by zixia
// console memory leak.
		    //self::write(str_replace('),', "),\n", var_export(debug_backtrace(), true)));

		    //trigger_error('triggered by user', E_USER_NOTICE);
		}
		/**
		 * Reenable debug output
		 *
		 */
		static function enable()
		{
		    if (self::$debug_switch == -1) self::$debug_switch = 1;
		    self::write();
		}
		/**
		 * Disable debug output when the output is not disturbable
		 *
		 */
		static function disable()
		{
		    if (self::$debug_switch == 1) self::$debug_switch = -1;
		}
		/**
		 * Initialize debug environment
		 *
		 * @param bool $outputHtml
		 * @return string
		 */
		static function init($outputHtml = true, $forceFlush = false )
		{
			// for static call JWDebug::init()
			self::instance();

		    if (self::$debug_switch != 0) return '';
		    self::$debug_switch = 1;
			self::$forceFlush = $forceFlush;
		    switch (DEBUG_DISPLAY_MODE) {
				case 'NATIVE':
				    $html = '<script type="text/javascript">__LOG__=window.console?(console.raw?console.raw:console.info):(window.opera?opera.postError:(window.Debug?Debug.writeln:function(s){try{if(!$("__C")){var d=document.createElement("div");d.id="__C";if (document.body)document.body.appendChild(d);else{alert(s);return;}}$("__C").appendChild(document.createTextNode(s));}catch(err){}}));</script>';
				    break;
				case 'JSLOG':
				    $html = '<script type="text/javascript" src="/js/jslog.js"></script>';
				    break;
				case 'XML':
				    $html = '';
				    break;
				case 'CONSOLE':
				    $html = '';
				    break;
				default:
				    $html = '';
		    }
		    if ($outputHtml) self::o($html);
		    return $html;
		}
		static public function dump($var, $return = false, $level = 0, $inObject = false)
		{
		    if ($level>7) return '[TOO DEEP WHEN DUMPING!!!]';
		    // Init
		    $indent      = '  ';
		    $doublearrow = ' => ';
		    $lineend     = ",\n";
		    $stringdelim = '\'';
		    $newline     = "\n";
		    $find		= array(null, '\\', '\'');
		    $replace     = array('NULL', '\\\\', '\\\'');
		    $out		 = '';
		    
		    // Indent
		    $level++;
		    for ($i = 1, $previndent = ''; $i < $level; $i++) {
				$previndent .= $indent;
		    }
		
		    $varType = gettype($var);
		
		    // Handle object indentation oddity
		    if ($inObject && $varType != 'object') {
				$previndent = substr($previndent, 0, -1);
		    }
		
		
		    // Handle each type
		    switch ($varType) {
				// Array
				case 'array':
				    if ($inObject) {
						$out .= $newline . $previndent;
				    }
				    $out .= 'array (' . $newline;
				    foreach ($var as $key => $value) {
						// Key
						if (is_string($key)) {
						    // Make key safe
						    $key = str_replace($find, $replace, $key);
						    $key = $stringdelim . $key . $stringdelim;
						}
						
						// Value
						if (is_array($value)) {
						    $export = self::dump($value, true, $level);
						    $value = $newline . $previndent . $indent . $export;
						} else {
						    $value = self::dump($value, true, $level);
						}
		
						// Piece line together
						$out .= $previndent . $indent . $key . $doublearrow . $value . $lineend;
				    }
		
				    // End string
				    $out .= $previndent . ')';
				    break;
		
				// String
				case 'string':
				    // Make the string safe
				    for ($i = 0, $c = count($find); $i < $c; $i++) {
						$var = str_replace($find[$i], $replace[$i], $var);
				    }
				    $out = $stringdelim . $var . $stringdelim;
				    break;
		
				// Number
				case 'integer':
				case 'double':
				    $out = (string) $var;
				    break;
				
				// Boolean
				case 'boolean':
				    $out = $var ? 'true' : 'false';
				    break;
		
				// NULLs
				case 'NULL':
				case 'resource':
				    $out = 'NULL';
				    break;
		
				// Objects
				case 'object':
				    // Start the object export
				    $out = $newline . $previndent;
				    $out .= get_class($var) . '::__set_state(array(' . $newline;
				    // Export the object vars
				    foreach(get_object_vars($var) as $key => $value) {
						$out .= $previndent . $indent . ' ' . $stringdelim . $key . $stringdelim . $doublearrow;
						$out .= self::dump($value, true, $level, true) . $lineend;
				    }
				    $out .= $previndent . '))';
				    break;
		    }
		
		    // Method of output
		    if ($return === true) {
				return $out;
		    } else {
				self::o($out);
		    }
		}
    }
    JWDebug::instance();
} else { // don't DEBUG
    class JWDebug {
		static function log() {}
		static function write() {}
		static function trace() {}
		static function enable() {}
		static function disable() {}
		static function init() {}
		static function dump($var, $return = false, $level = 0, $inObject = false) { return var_export($var, $return); }
    }
}
?>
