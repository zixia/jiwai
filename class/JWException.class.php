<?php
/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      zixia@zixia.net
 * @version     $Id$
 */

if (!defined('ERROR_LOGFILE'))
/**
 *  Log file name
 *
 */
define('ERROR_LOGFILE', '/tmp/ERR_{Date}_{Type}_{Code}.log');

if (!defined('EXCEPTION_DISPLAY'))
/**
 *  Log exception, true ON (default) / false OFF
 *
 */
define('EXCEPTION_DISPLAY', true);

if (!defined('EXCEPTION_LOG'))
/**
 *  Log exception, true ON (default) / false OFF
 *
 */
define('EXCEPTION_LOG', true);

if (!defined('ERROR_DISPLAY'))
/**
 *  Log error, true ON (default) / false OFF
 *
 */
define('ERROR_DISPLAY', true);

if (!defined('ERROR_LOG'))
/**
 *  Log error, true ON (default) / false OFF
 *
 */
define('ERROR_LOG', true);


if (EXCEPTION_DISPLAY || EXCEPTION_LOG) {
    set_exception_handler(array('JWException', 'exception_handler'));
}

if (ERROR_DISPLAY || ERROR_LOG) {
    //error_reporting(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
    set_error_handler(array('JWException', 'error_handler'), E_ALL);
}

/**
 * JiWai.de Exception Class
 *
 */
class JWException extends Exception
{
    const OBSERVER_PRINT = -2;
    const OBSERVER_TRIGGER = -4;
    const OBSERVER_DIE = -8;
    protected $cause;
    private static $_observers = array();
    private static $_uniqueid = 0;
    private $_trace;
    public $_error_file;
    public $_error_line;
    public $_error_context;

    /**
     * Supported signatures:
     * JWException(string $message);
     * JWException(string $message, int $code);
     * JWException(string $message, Exception $cause);
     * JWException(string $message, Exception $cause, int $code);
     * JWException(string $message, array $causes);
     * JWException(string $message, array $causes, int $code);
     */
    public function __construct($message=0, $p2 = null, $p3 = null)
    {
        if (is_int($message)) {
            $t = $p2 ? $p2 : '';
            $p2 = $message;
            $message = $t;
        }
        if (is_int($p2)) {
            $code = $p2;
            $this->cause = null;
        } elseif ($p2 instanceof Exception || is_array($p2)) {
            $code = $p3;
            if (is_array($p2) && isset($p2['message'])) {
                // fix potential problem of passing in a single warning
                $p2 = array($p2);
            }
            $this->cause = $p2;
        } else {
            $code = null;
            $this->cause = null;
        }
        parent::__construct($message, $code);
        $this->signal();
    }

    /**
     * @param mixed $callback  - A valid php callback, see php func is_callable()
     *                         - A JWException::OBSERVER_* constant
     *                         - An array(const JWException::OBSERVER_*,
     *                           mixed $options)
     * @param string $label    The name of the observer. Use this if you want
     *                         to remove it later with removeObserver()
     */
    public static function addObserver($callback, $label = 'default')
    {
        self::$_observers[$label] = $callback;
    }

    public static function removeObserver($label = 'default')
    {
        unset(self::$_observers[$label]);
    }

    /**
     * @return int unique identifier for an observer
     */
    public static function getUniqueId()
    {
        return self::$_uniqueid++;
    }

    private function signal()
    {
        foreach (self::$_observers as $func) {
            if (is_callable($func)) {
                call_user_func($func, $this);
                continue;
            }
            settype($func, 'array');
            switch ($func[0]) {
                case self::OBSERVER_PRINT :
                    $f = (isset($func[1])) ? $func[1] : '%s';
                    printf($f, $this->getMessage());
                    break;
                case self::OBSERVER_TRIGGER :
                    $f = (isset($func[1])) ? $func[1] : E_USER_NOTICE;
                    trigger_error($this->getMessage(), $f);
                    break;
                case self::OBSERVER_DIE :
                    $f = (isset($func[1])) ? $func[1] : '%s';
                    die(printf($f, $this->getMessage()));
                    break;
                default:
                    trigger_error('invalid observer type', E_USER_WARNING);
            }
        }
    }

    /**
     * Return specific error information that can be used for more detailed
     * error messages or translation.
     *
     * This method may be overridden in child exception classes in order
     * to add functionality not present in JWException and is a placeholder
     * to define API
     *
     * The returned array must be an associative array of parameter => value like so:
     * <pre>
     * array('name' => $name, 'context' => array(...))
     * </pre>
     * @return array
     */
    public function getErrorData()
    {
        return array();
    }

    /**
     * Returns the exception that caused this exception to be thrown
     * @access public
     * @return Exception|array The context of the exception
     */
    public function getCause()
    {
        return $this->cause;
    }

    /**
     * Function must be public to call on caused exceptions
     * @param array
     */
    public function getCauseMessage(&$causes)
    {
        $trace = $this->getTraceSafe();
        $cause = array('class'   => get_class($this),
                       'message' => $this->message,
                       'file' => 'unknown',
                       'line' => 'unknown');
        if (!empty($this->_error_file)) {
            $cause['class'] = $this->getErrorType();
            $cause['file'] = $this->_error_file;
            $cause['line'] = $this->_error_line;
        } else
        if (isset($trace[0])) {
            if (isset($trace[0]['file'])) {
                $cause['file'] = $trace[0]['file'];
                $cause['line'] = $trace[0]['line'];
            }
        }
        $causes[] = $cause;
        if ($this->cause instanceof JWException) {
            $this->cause->getCauseMessage($causes);
        } elseif ($this->cause instanceof Exception) {
            $causes[] = array('class'   => get_class($cause),
                           'message' => $cause->getMessage(),
                           'file' => $cause->getErrorFile(),
                           'line' => $cause->getErrorLine());

        } elseif (is_array($this->cause)) {
            foreach ($this->cause as $cause) {
                if ($cause instanceof JWException) {
                    $cause->getCauseMessage($causes);
                } elseif ($cause instanceof Exception) {
                    $causes[] = array('class'   => get_class($cause),
                                   'message' => $cause->getMessage(),
                                   'file' => $cause->getErrorFile(),
                                   'line' => $cause->getErrorLine());
                } elseif (is_array($cause) && isset($cause['message'])) {
                    // ErrorStack warning
                    $causes[] = array(
                        'class' => $cause['package'],
                        'message' => $cause['message'],
                        'file' => isset($cause['context']['file']) ?
                                            $cause['context']['file'] :
                                            'unknown',
                        'line' => isset($cause['context']['line']) ?
                                            $cause['context']['line'] :
                                            'unknown',
                    );
                }
            }
        }
    }

    public function getTraceSafe()
    {
        if (!isset($this->_trace)) {
            $this->_trace = $this->getTrace();
            if (empty($this->_trace)) {
                $backtrace = debug_backtrace();
                $this->_trace = array($backtrace[count($backtrace)-1]);
            }
        }
        return $this->_trace;
    }

    public function getErrorFile() {
        return empty($this->_error_file) ? parent::getFile() : $this->_error_file;
    }

    public function getErrorLine() {
        return empty($this->_error_line) ? parent::getFile() : $this->_error_line;
    }

    public function getErrorClass()
    {
        $trace = $this->getTraceSafe();
        return $trace[0]['class'];
    }

    public function getErrorMethod()
    {
        $trace = $this->getTraceSafe();
        return $trace[0]['function'];
    }

    public function __toString()
    {
        return $this->toText(); //CONSOLE ? $this->toText() : $this->toHtml();
    }

    public function toHtml()
    {
        $trace = $this->getTraceSafe();
        $causes = array();
        $this->getCauseMessage($causes);
        $html =  '<table border="0" cellspacing="0">' . "\n";
        foreach ($causes as $i => $cause) {
            $html .= '<tr><td colspan="3" bgcolor="#ff6666">'
               . str_repeat('-', $i) . ' <b>' . $cause['class'] . '</b>: '
               . htmlspecialchars($cause['message']) . ' in <b>' . $cause['file'] . '</b> '
               . 'on line <b>' . $cause['line'] . '</b>'
               . "</td></tr>\n";
        }
        $html .= '<tr><td colspan="3" bgcolor="#aaaaaa" align="center"><b>Trace</b></td></tr>' . "\n"
               . '<tr><td align="center" bgcolor="#cccccc" width="20"><b>#</b></td>'
               . '<td align="center" bgcolor="#cccccc"><b>Function</b></td>'
               . '<td align="center" bgcolor="#cccccc"><b>Location</b></td></tr>' . "\n";

        foreach ($trace as $k => $v) {
            if (isset($v['class'])) { if ($v['class']=='JWException' && $v['type']=='::') continue; }
            $html .= '<tr><td align="center" style="border-bottom: 1px solid">' . $k . '</td>'
                   . '<td style="border-bottom: 1px solid">';
            if (!empty($v['class'])) {
                $html .= $v['class'] . $v['type'];
            }
            $html .= $v['function'];
            $args = array();
            if (!empty($v['args'])) {
                foreach ($v['args'] as $arg) {
                    if (is_null($arg)) $args[] = 'null';
                    elseif (is_array($arg)) $args[] = 'Array';
                    elseif (is_object($arg)) $args[] = 'Object('.get_class($arg).')';
                    elseif (is_bool($arg)) $args[] = $arg ? 'true' : 'false';
                    elseif (is_int($arg) || is_double($arg)) $args[] = $arg;
                    else {
                        $arg = (string)$arg;
                        $str = htmlspecialchars(substr($arg, 0, 32));
                        if (strlen($arg) > 32) $str .= '&hellip;';
                        $args[] = "'" . $str . "'";
                    }
                }
            }
            $html .= '(' . implode(', ',$args) . ')'
                   . '</td>'
                   . '<td style="border-bottom: 1px solid">' . (isset($v['file']) ? $v['file'] : 'unknown')
                   . ':' . (isset($v['line']) ? $v['line'] : 'unknown')
                   . '</td></tr>' . "\n";
        }
        $html .= '<tr><td align="center">' . ($k+1) . '</td>'
               . '<td>{main}</td>'
               . '<td>&nbsp;</td></tr>' . "\n";
        if (is_array($this->_error_context)) {
            $html .= '<tr><td colspan="3" bgcolor="#aaaaaa" align="center"><b>Context</b></td></tr>' . "\n"
               . '<tr><td align="center" bgcolor="#cccccc" colspan="2"><b>Variable</b></td>'
               . '<td align="center" bgcolor="#cccccc"><b>Value</b></td></tr>' . "\n";
            foreach ($this->_error_context as $n => $v) {
                $html .= '<tr><td colspan="2">$' . $n . '</td>'
                   . '<td>'. var_export($v, true) . '</td></tr>' . "\n";
            }
        }
        $html .= '</table>';
        return $html;
    }

    public function toText()
    {
        $causes = array();
        $this->getCauseMessage($causes);
        $causeMsg = '';
        foreach ($causes as $i => $cause) {
            $causeMsg .= str_repeat(' ', $i) . $cause['class'] . ': '
                   . $cause['message'] . ' in ' . $cause['file']
                   . ' on line ' . $cause['line'] . "\n";
        }
        return "\n".$causeMsg . $this->getTraceAsString()."\n";
    }

    static function exception_handler(Exception $exception) {
        if ($exception instanceof JWException) {
            $causeMsg = $exception->toText();
            if (EXCEPTION_DISPLAY) print (CONSOLE ? JWConsole::convert('%R').$exception->toText().JWConsole::convert('%n') : $exception->toHtml());
        } else {
            $causeMsg = get_class($exception) . ': '
                   . $exception->getMessage() . ' in ' . $exception->getFile()
                   . ' on line ' . $exception->getLine() . "\n" .$exception->getTraceAsString();
            if (EXCEPTION_DISPLAY) print '<pre>'.$causeMsg.'</pre>';
        }
        if (EXCEPTION_LOG) self::logtofile($exception);
        if (DEBUG) {
            JWDebug::write($causeMsg);
        }
    }

    static function error_handler($errno, $errstr, $errfile, $errline, $context) {
        if (strpos($errstr, 'Cannot modify header information')!==false) return;
        $e = new JWException($errstr, $errno);
        $e->_error_file = $errfile;
        $e->_error_line = $errline;
        $e->_error_context = $context;
        if (ERROR_DISPLAY) print $e;
        if (ERROR_LOG) self::logtofile($e);
        if (DEBUG && !CONSOLE) {
            JWDebug::write($e->toText());
        }
        //if ($errno == 0) die('Fatal');
    }

    public function getErrorType() {
        $a = array(0 => 'E_NONE', 1 => 'E_ERROR', 2 => 'E_WARNING', 4 => 'E_PARSE', 8 => 'E_NOTICE',
            16 => 'E_CORE_ERROR', 32 => 'E_CORE_WARNING', 64 => 'E_COMPILE_ERROR',
            128 => 'E_COMPILE_WARNING', 256 => 'E_USER_ERROR', 512 => 'E_USER_WARNING',
            1024 => 'E_USER_NOTICE', 2047 => 'E_ALL', 2048 => 'E_STRICT');
        return $a[$this->code];
    }

    static function logtofile($e) {
	self::$__lastException = $e;
        $f = preg_replace_callback('#{([^{}]+)}#', get_class($e)=='JWException' ? array($e,'__fillInfo') : array(__CLASS__, '__fillInfo__'), ERROR_LOGFILE);
        $s = '['.date(DATE_ATOM).'] Uncaught exception or error: '.$e->getCode().
             "\nMESSAGE\t".$e->getMessage().
             "\nPID\t".getmypid().
             (CONSOLE ? "\nCMD\t".JWConsole::cmdline() :
             "\nCLIENT\t".$_SERVER['REMOTE_ADDR'].
             "\nURI\t".$_SERVER['REQUEST_URI'].
             "\nREFERER\t".(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"None")).
             //"\nLASTSQL\t".JWDBSource::$last_sql.
             "\nTRACE\n".$e->getTraceAsString().
             "\n------------------------------- [Logged by JWException \$Rev: 150 $] ----\n";
        error_log($s, 3, $f);
    }

    public function __fillInfo($m) {
        $s = '';
        switch (strtolower($m[1])) {
            case 'cmd':
            case 'uri':
                $s = urlencode(CONSOLE ? JWConsole::cmdline() : $_SERVER['REQUEST_URI']);
                break;
            case 'referer':
                $s = urlencode(CONSOLE ? '' : isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "None");
                break;
            case 'file':
                $s = '';
                break;
            case 'code':
                $s = $this->getCode();
                break;
            case 'type':
                $s = get_class($this);
                if (!empty($this->_error_file)) $s = $this->getErrorType();
                break;
            case 'text':
                $s = $this->getMessage();
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
    public static $__lastException;
    public static function __fillInfo__($m) {
        $s = '';
        switch (strtolower($m[1])) {
            case 'cmd':
            case 'uri':
                $s = urlencode(CONSOLE ? JWConsole::cmdline() : $_SERVER['REQUEST_URI']);
                break;
            case 'referer':
                $s = urlencode(CONSOLE ? '' : isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "None");
                break;
            case 'file':
                $s = '';
                break;
            case 'code':
                $s = self::$__lastException->getCode();
                break;
            case 'type':
                $s = get_class(self::$__lastException);
                break;
            case 'text':
                $s = self::$__lastException->getMessage();
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
}

?>
