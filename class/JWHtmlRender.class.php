<?php
/*
 * @author: Seek
 */

if(!defined('TPL_FILE_SUFFIX')) define('TPL_FILE_SUFFIX','.tpl');
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR','/tmp');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR','/tmp');

class JWHtmlRender extends Template_Base{
	public function render($templateFile, $v=array() ){
		return parent::render(TPL_TEMPLATE_DIR.'/'.$templateFile, $v);
	}
	public function display($templateFile,$v=array() ){
		echo $this->render($templateFile, $v );
	}
}

class Template_Base{   
	
	//Next change to extends FrameBase support __get(),__set(),__call(); 2006-10-06 fixed
	//now have fixed the warning to access not exists properties;
	//used to store value stack of template calling especiall call [part]
        protected $_properties_array_used_by_overload = array();
	public static $lastContent = null;

        public function __get($name=null){
                return isset($this->_properties_array_used_by_overload[$name]) ?
                        $this->_properties_array_used_by_overload[$name] : false;
        }

        public function __set($name=null,$value=null){
                return $this->_properties_array_used_by_overload[$name] = $value;
        }

        public function __call($method=null,$param = array()){
                return false;
        }
	
	//following is for template	

	public function __construct(){}
	public function __destruct(){
		$this->_properties_array_used_by_overload = null;
	}

	private function __parsecall($matches) {
		return '<?= $render("'.$matches[1].'") ?>';
	}

	private function parse($templateFile,$compiledFile) {

		$fileContent = false;

		if(!($fileContent = file_get_contents($templateFile)))
			return false;

		$fileContent = preg_replace("/\<\!\-\-\s*\\\$\{(.+?)\}\s*\-\-\>/ies", "\$this->__replace('<? \\1; ?>')", $fileContent);
		$fileContent = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\\\ \-\'\,\%\*\/\.\(\)\'\"\$\x7f-\xff]+)\}/s", "<?= \\1 ?>", $fileContent);
		$fileContent = preg_replace("/\\\$\{(.+?)\}/ies", "\$this->__replace('<?= \\1 ?>')", $fileContent);
		$fileContent = preg_replace("/\<\!\-\-\s*\{else\s*if\s+(.+?)\}\s*\-\-\>/ies", "\$this->__replace('<? } else if(\\1) { ?>')", $fileContent);
		$fileContent = preg_replace("/\<\!\-\-\s*\{else\}\s*\-\-\>/is", "<? } else { ?>", $fileContent);

		for($i = 0; $i < 5; ++$i) {
			$fileContent = preg_replace("/\<\!\-\-\s*\{foreach\s+(\S+)\s+as\s+(\S+)\s*=>\s*(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/foreach\}\s*\-\-\>/ies", "\$this->__replace('<? if(is_array(\\1)){foreach(\\1 as \\2=>\\3) { ?>\\4<? }}?>')", $fileContent);
			$fileContent = preg_replace("/\<\!\-\-\s*\{foreach\s+(\S+)\s+as\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/foreach\}\s*\-\-\>/ies", "\$this->__replace('<? if(is_array(\\1)){foreach(\\1 as \\2) { ?>\\3<? }}?>')", $fileContent);
			$fileContent = preg_replace("/\<\!\-\-\s*\{if\s+(.+?)\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/if\}\s*\-\-\>/ies", "\$this->__replace('<? if(\\1){?>\\2<? }?>')", $fileContent);
		}
		
		//Add for call <!--{portal->part /video/index/}-->
		$fileContent = preg_replace("/\<\!\-\-\s*\{\s*(\w+)\-\>(\w+)\s+(.+?)\s*}\s*\-\-\>/is","<?= $\\1->\\2('\\3') ?>",$fileContent);
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

	public function render($templateFile,$valueArray=array()) {
		$templateFile = $templateFile . TPL_FILE_SUFFIX;	
		$compiledFile = TPL_COMPILED_DIR .'/'. md5($templateFile) . '.php';

		if(count($valueArray)){
			foreach($valueArray as $key=>$value)
			       $this->$key = $value;
			$this->_SESSION = isset($_SESSION) ? $_SESSION : array();
		}
		
		if(false===file_exists($templateFile)){
			throw new Exception("Templace File [$templateFile] Not Found!");
		}

		if(false===file_exists($compiledFile) 
				|| @filemtime($templateFile) > @filemtime($compiledFile)) {
			$this->parse($templateFile,$compiledFile);
		}

		ob_start();
		require($compiledFile);
		return self::$lastContent = ob_get_clean();
	}
}
?>
