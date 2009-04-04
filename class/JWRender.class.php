<?php
/**
 * @author: shwdai@gmail.com
 */
if(!defined('TPL_FILE_SUFFIX')) define('TPL_FILE_SUFFIX','.tpl');
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR','/tmp');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR','/tmp');
class JWRender{
	static private $mTemplate = null;
	static private function GetTemplate(){
		return null==self::$mTemplate ? 
			self::$mTemplate = new Template_Render() : self::$mTemplate;
	}
	static private function CloseTemplate(){
		self::$mTemplate = null;
	}

	static public function Render($templateFile, $v=array(), $display=false){
		$templateFile = preg_replace('/'.TPL_FILE_SUFFIX.'$/', '', $templateFile);
		$content = self::GetTemplate()->render( $templateFile, $v, $display );
		self::CloseTemplate();
		return $content;
	}

	static public function Display( $templateFile,$v=array() ){
		self::Render( $templateFile, $v, true);
	}

	static public function Assign($k=null, $v=null){
		self::GetTemplate()->assign($k, $v);
	}

	static public function GetLastContent(){
		return Template_Render::$lastContent;
	}

	static public function Clear(){
		self::CloseTemplate();
	}

	static public function Exist( $templateFile ) {
		$templateFile = preg_replace('/'.TPL_FILE_SUFFIX.'$/', '', $templateFile);
		return Template_Render::Exist( $templateFile );
	}

	static public function SetModule($module=null){
		Template_Render::$module = $module;
	}
}

class Template_Render{ 

	protected $_properties_array_used_by_overload = array();
	public static $lastContent = null;
	public static $module = null;

	public function __get($name=null){
		switch($name){
			case '_SESSION':
				return isset($_SESSION) ? $_SESSION : false;
			case '_SERVER':
				return isset($_SERVER) ? $_SERVER : false;
			case '_REQUEST':
				return isset($_REQUEST) ? $_REQUEST : false;
			case '_GET':
				return isset($_GET) ? $_GET : false;
			case '_COOKIE':
				return isset($_COOKIE) ? $_COOKIE : false;
			case '_POST':
				return isset($_POST) ? $_POST : false;
			default:
				return isset($this->_properties_array_used_by_overload[$name]) ?
					$this->_properties_array_used_by_overload[$name] : false;
		}
	}

	public function __set($name=null,$value=null){
		return $this->_properties_array_used_by_overload[$name] = $value;
	}

	public function __call($method=null,$param = array()){
		return false;
	}

	//following is for template	

	public function __construct(){
		global $g_page_user_id;
		global $g_current_user_id;
		$g_current_user_id = JWLogin::GetCurrentUserId();
		$this->_INI = JWConfig::ini();
		if ( $g_current_user_id ) {
			$this->g_current_user = JWUser::GetUserInfo($g_current_user_id);
		}

		if ( false==$g_page_user_id) {
			$g_page_user_id = $g_current_user_id;
			$this->g_page_user = $this->g_current_user;
		} else {
			$this->g_page_user = JWUser::GetUserInfo($g_page_user_id);
		}

		$script_url = $_SERVER['REQUEST_URI'];
		$this->g_page_on = !preg_match('#^/(wo|g|t|k)/#i',$script_url);
		$this->g_tag_on = preg_match('#/t/#i', $script_url);
		$this->g_page_user_id = $g_page_user_id;
		$this->g_current_user_id = $g_current_user_id;
		$this->element = JWElement::Instance();
	}

	public function __destruct(){
		$this->_properties_array_used_by_overload = null;
	}

	private function __parsecall($matches) {
		return '<?php echo $render("'.$matches[1].'") ?>';
	}

	private function parse($templateFile,$compiledFile) {

		$fileContent = false;

		if(!($fileContent = file_get_contents($templateFile)))
			return false;

		$fileContent = preg_replace("/\<\!\-\-\s*\\\$\{(.+?)\}\s*\-\-\>/ies", "\$this->__replace('<?php \\1; ?>')", $fileContent);
		$fileContent = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\\\ \-\'\,\%\*\/\.\(\)\'\"\$\x7f-\xff]+)\}/s", "<?php echo \\1 ?>", $fileContent);
		$fileContent = preg_replace("/\\\$\{(.+?)\}/ies", "\$this->__replace('<?php echo \\1 ?>')", $fileContent);
		$fileContent = preg_replace("/\<\!\-\-\s*\{else\s*if\s+(.+?)\}\s*\-\-\>/ies", "\$this->__replace('<?php } else if(\\1) { ?>')", $fileContent);
		$fileContent = preg_replace("/\<\!\-\-\s*\{elif\s+(.+?)\}\s*\-\-\>/ies", "\$this->__replace('<?php } else if(\\1) { ?>')", $fileContent);
		$fileContent = preg_replace("/\<\!\-\-\s*\{else\}\s*\-\-\>/is", "<?php } else { ?>", $fileContent);

		for($i = 0; $i < 5; ++$i) {
			$fileContent = preg_replace("/\<\!\-\-\s*\{foreach\s+(\S+)\s+as\s+(\S+)\s*=>\s*(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/foreach\}\s*\-\-\>/ies", "\$this->__replace('<?php if(is_array(\\1)){foreach(\\1 as \\2=>\\3) { ?>\\4<?php }}?>')", $fileContent);
			$fileContent = preg_replace("/\<\!\-\-\s*\{foreach\s+(\S+)\s+as\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/foreach\}\s*\-\-\>/ies", "\$this->__replace('<?php if(is_array(\\1)){foreach(\\1 as \\2) { ?>\\3<?php }}?>')", $fileContent);
			$fileContent = preg_replace("/\<\!\-\-\s*\{if\s+(.+?)\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/if\}\s*\-\-\>/ies", "\$this->__replace('<?php if(\\1){?>\\2<? }?>')", $fileContent);
		}

		//Add for call <!--{portal->part /video/index/}-->
		$fileContent = preg_replace("/\<\!\-\-\s*\{\s*(\w+)\-\>(\w+)\s+(.+?)\s*}\s*\-\-\>/is","<?php echo $\\1->\\2('\\3') ?>",$fileContent);
		$fileContent = preg_replace_callback("/\<\!\-\-\s*\{\s*include\s+(.+?)\s*}\s*\-\-\>/is", array(&$this,'__parsecall'), $fileContent);

		//Add value namespace
		$fileContent = preg_replace_callback("/<\?(.+?)\?>/s", array(&$this,'__callback'), $fileContent);
		if(!file_put_contents($compiledFile,$fileContent))	
			return false;

		return true;
	}

	private function __callback($matches){
		return preg_replace("/\\\$([a-zA-Z0-9_\'\"\$\x7f-\xff]+)/s", "\$this->\\1", $matches[0]);
	}
	private function __replace($string) {
		return str_replace('\"', '"', $string);
	}

	public function assign($k=null, $v=null){
		if ( is_array($k) ) { foreach( $k AS $key=>$value ) { $this->$key = $value; } }
		else $this->$k = $v;
	}

	private function realTemplateFile($templateFile){
		if ( self::$module )
			return TPL_TEMPLATE_DIR.'/'.self::$module.'/'.$templateFile.TPL_FILE_SUFFIX;	
		return TPL_TEMPLATE_DIR.'/'.$templateFile.TPL_FILE_SUFFIX;	
	}

	public function exist($templateFile) {
		return file_exists( $this->realTemplateFile($templateFile) );
	}

	public function render($templateFile,$valueArray=array(), $display=true) {

		$templateFile = $this->realTemplateFile($templateFile);
		$compiledFile = TPL_COMPILED_DIR.'/'.md5($templateFile).'.php';

		$this->assign( $valueArray );

		if(false===file_exists($templateFile)){
			throw new Exception("Templace File [$templateFile] Not Found!");
		}

		if(false===file_exists($compiledFile) 
				|| @filemtime($templateFile) > @filemtime($compiledFile)) {
			$this->parse($templateFile,$compiledFile);
		}

		if ( false == $display ) {
			ob_start();
			require($compiledFile);
			self::$lastContent = ob_get_clean();
			ob_end_flush();
			return self::$lastContent;
		} else {
			require($compiledFile);
		}
	}
}
?>
