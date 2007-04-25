<?php
/**
 * @package     Novajax
 * @copyright   1Verge Inc.
 * @author      FreeWizard
 * @version     $Id: NovaResponse.class.php 356 2007-03-02 09:42:45Z freewizard $
 */

 /**
  * NovaResponse Class
  *
  */
class NovaResponse {
    /**
     * Instance of this singleton
     *
     * @var NovaResponse
     */
    static private $instance__;

    /**
     * Instance of this singleton class
     *
     * @return NovaResponse
     */
    static public function &instance ()
    {
        if (!isset(self::$instance__)) {
            $class = __CLASS__;
            self::$instance__ = new $class;
        }
        return self::$instance__;
    }

    /**
     * Cache mode
     *
     * @var mixed
     */
    protected $cachemode = null;

    /**
     * Prepare a Smarty object
     *
     * @return Smarty
     */
    protected function &getEngine(&$module = null) {
        $helper = new NovaRenderHelper();
        $helper->module = &$module;
        $smarty = new Smarty();
        $smarty->compile_check = true;

        # by zixia k60707 $smarty->template_dir = NOVA_ROOT;
        $smarty->template_dir = MODULE_ROOT;

        $smarty->compile_dir = TPLCACHE_PATH;
        $smarty->caching = 0;
        $smarty->register_object('nova', $helper, null, true, NovaRenderHelper::$blocks);
        $smarty->plugins_dir = array('plugins', NOVA_ROOT.'/WebControls/plugins');
        //$smarty->assign_by_ref("page", $this);
        return $smarty;
    }

    public function __construct() {
        ignore_user_abort(true);
        //set_time_limit(0); //XXX: Be aware!!
    }

    public function __destruct() {
        if (count($this->headers)) $this->output();
    }

    /**
     * Check if template exists.
     *
     * @param NovaModule $module
     * @param string $template
     * @return bool
     */
    public function templateExists($module, $template){
        return file_exists(($module ? MODULE_ROOT."/$module/tpl/$template" : NOVA_ROOT."/tpl/$template"));
    }

    /**
     * Render according to a template
     *
     * @param NovaModule $module
     * @param string $template
     * @param array $parameters
     * @return string
     */
    public function render($module, $template, $parameters = array()) {
        if($module){
            $template = $module->getName__()."/tpl/$template";
            if(strpos('../../pub/',$template)!==false){
                $template=realpath($template);
            }
        }
        $engine = $this->getEngine($module);
        foreach ($parameters as $varname => $value) {
            if (!strpos($varname, "__")) {
                $engine->assign($varname, $value);
            }
        }
        $html = $engine->fetch($template);
        return $html;
    }

    /**
     * Display according to a template
     *
     * @param string $template
     * @param array $parameters
     */
    public function display($template, $parameters = array()) {
        $engine = $this->getEngine();
        foreach ($parameters as $varname => $value) {
            if (!strpos($varname, "__")) {
                $engine->assign($varname, $value);
            }
        }
        $engine->display($template);
    }

    /**
     * Do a 302
     *
     * @param string $url
     * @param bool $exit
     */
    public function redirect($url, $exit = true) {
        header("Location: $url");
        if ($exit) die();
    }

    /**
     * Output as page result
     *
     * @param string $str
     */
    public function output($str='') {
        if (is_null($this->cachemode)) {
            $this->cache_header("nocache");
        }
        if  (NovaRequest::instance()->isDSR) {
            //die('alert('.$_SERVER['CALLBACK_ID']."._parse({ 'success': '1', 'responseHeaders': { 'Content-Type': 'text/plain' }, 'status': '200', 'statusText': 'OK', 'responseText': '' }))");
            header('Content-Type: text/javascript');
            $str = $this->respond($this->parseData($str));
        }
        $this->header('Content-Type: '.$this->contentType);
        $this->_output($str);
    }

    /**
     * Output header and content
     * @internal
     *
     * @param string $str
     */
    protected function _output($str){
        foreach ($this->headers as $h) {
            @header($h);
        }
        $this->headers = array();
        echo $str;
    }

    /**
     * Output an error
     *
     * @param int $code
     * @param string $msg
     */
    public function displayError($code=0, $msg='') {
        if (!$code) $code = 500;
        try {
            @header('HTTP/1.0 '.($code>=400 ? $code : 500).' Novajax Error');
        } catch (Exception $e) {
        }
        try {
            $path = (string) NovaConfig::instance()->default_module;
           	$pathMapper = NovaConfig::instance()->modules_path;
            if ($pathMapper) if ($pathMapper->$path) $path = (string) $pathMapper->$path;
            $path = MODULE_ROOT . '/' . $path . '/tpl/';
            if (!file_exists($path.'error.tpl')) $path = NOVA_ROOT . '/tpl/';
        } catch (Exception $e) {
            $path = NOVA_ROOT . '/tpl/';
        }
        $f = $code.'.tpl';
        if (!file_exists($path.$f)) $f = 'error.tpl';
        $engine = $this->getEngine($module);
        $engine->assign('code', $code);
        $engine->assign('message', $msg);
        $engine->display($path.$f);
    }

    /**
     * Header lines holded
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Set header
     * NOTE: use this instead of system header()
     *
     * @param string $s
     */
    public function header($s) {
        $this->headers[] = $s;
    }

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * Set content type
     * NOTE: use this instead of system header()
     *
     * @param string $s
     */
    public function setContentType($s) {
        $this->contentType = $s;
    }

    /**
     * Output json data via header
     *
     * @param mixed $data
     */
    public function outputJSON($data) {
        $data = NovaJSON::encode($data);
        $this->header('X-JSON: '.$data);
        echo $data;
    }

    /**
     * Check cache, 304 if not modified
     *
     * @param int $modifytime
     */
    function cacheCheck($modifytime) {
        if (DEBUG) exit;
        if ($this->cache_header("public",$modifytime,10)) exit;
    }

    /**
     * Cache internal
     *
     * @param string $scope
     * @param int $forcecachetime
     * @param int $modifytime
     * @param int $expiretime
     * @return bool
     */
    private function cache_process($scope, $forcecachetime, $modifytime, $expiretime) {
        //session_cache_limiter($scope);
        $this->cachemode=$scope;
        if ($scope=="nocache" || $scope=="no-cache") {
            @header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
            @header("Cache-Control: no-store, no-cache, must-revalidate");
            @header("Pragma: no-cache");
            return FALSE;
        }
        $oldmodified=isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) ? $_SERVER["HTTP_IF_MODIFIED_SINCE"] : '';
        if ($oldmodified!="") {
            $oldtime = strtotime($oldmodified) + $forcecachetime;
        } else $oldtime=0;
        if ($oldtime >= $modifytime) {
            @header("HTTP/1.1 304 Not Modified");
            @header("Cache-Control: max-age=" . "$expiretime");
            return TRUE;
        }
        @header("Pragma:");
        @header("Last-Modified: " . gmdate("D, d M Y H:i:s", $modifytime) . " GMT");
        @header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expiretime) . " GMT");
        @header("Cache-Control: max-age=" . "$expiretime");
        return FALSE;
    }

    public function cache_forever() {
        //$this->header('Cache-Control: max-age='.(180*24*60*60));
        return $this->cache_process('public', 0, time()-180, 180*24*60*60);
    }
    /**
     * Cache internal
     *
     * @param string $scope
     * @param int $modifytime
     * @param int $expiretime
     * @return bool
     */
    function cache_header($scope,$modifytime=0,$expiretime=300)
    {
        return $this->cache_process($scope, 0, $modifytime, $expiretime);
    }

    function update_cache_header_seconds($cache_seconds = 10)
    {
        $now = time();
        return $this->cache_process("public", $cache_seconds, $now, $cache_seconds);
    }

    /**
     * Cache internal
     *
     * @param int $updatetime
     * @param int $expiretime
     * @return bool
     */
    function update_cache_header($updatetime = 10,$expiretime = 300)
    {
        return $this->cache_process("public", 60 * $updatetime, time(), $expiretime);
    }

    /**
     * DSR internal
     *
     * @param string $data
     * @return array
     */
    protected function parseData($data) {
        $result = Array();
        $result['success'] = true;

        foreach ($this->headers as $h) {
            $hh = explode(':', $h, 2);
            $result['responseHeaders'][trim($hh[0])] = trim($hh[1]);
        }
        $result['responseHeaders']['Content-Type'] = 'text/plain'; //FIXME: should be set outside

        $result['status'] = 200;
        $result['statusText'] = 'OK';

        /*
         if($result['responseHeaders']['Content-Encoding'] == 'gzip') {
         $data = substr($data, 10); $data = gzinflate($data);
         }

         $data = trim($data);

         $charset = explode('=', $result['responseHeaders']['Content-Type']);
         $charset = strtolower($charset[1]);
         if($charset == "windows-1251" && is_callable('iconv'))
         $data = iconv("windows-1251", "utf-8", $data);
         */
        $data = str_replace("\r", '\r', $data);
        $data = str_replace("\n", '\n', $data);
        $data = str_replace('\'', '\\\'', $data);
        $result["responseText"] = $data;

        return $result;
    }

    /**
     * DSR internal
     *
     * @param array $data
     * @return string
     */
    protected function respond($data) {
        $data = $this->parseResponse($data);
        return $_SERVER['CALLBACK_ID']."._parse(".$data.")";
    }

    /**
     * DSR internal
     *
     * @param array $data
     * @return string
     */
    protected function parseResponse($data)     {
        $output = "";
        foreach($data as $key=>$value) {
            $v = (is_array($value)) ? $this->parseResponse($value) : "'$value'";
            $output .= "\n'$key': $v, ";
        }
        $output = "{ ".substr($output, 0, -2). "\n }";
        return $output;
    }
}

?>
